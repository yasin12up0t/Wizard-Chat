<?php

// app/Http/Controllers/GroupController.php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Models\GroupMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class GroupController extends Controller
{
    /*
        User create groups and he becomes the ADMIN
        http://127.0.0.1:8000/groups/create
    */
    public function createGroup()
    {
        $userId = Auth::id();

        // Fetch all users who have had a conversation with the authenticated user
        $users = User::whereIn('id', function($query) use ($userId) {
            $query->select('from_user_id')
                ->from('chats')
                ->where('to_user_id', $userId)
                ->distinct()
                ->union(function($query) use ($userId) {
                    $query->select('to_user_id')
                        ->from('chats')
                        ->where('from_user_id', $userId)
                        ->distinct();
                });
        })
        ->where('id', '!=', $userId) // Exclude the current user
        ->get();

        return view('chat.index', compact('users'));
    }

    /*
        Show All groups that user is in it
        http://127.0.0.1:8000/groups/{group}
    */
    public function ShowUserGroups($id)
    {
        $group = Group::with('creator', 'users')->findOrFail($id);
        $isMember = $group->users()->where('user_id', auth()->id())->exists();

        $userId = Auth::id();

        // Fetch users who had a conversation with the authenticated user and are not part of the group
        $users = User::whereIn('id', function ($query) use ($userId) {
            $query->select('from_user_id')
                ->from('chats')
                ->where('to_user_id', $userId)
                ->distinct()
                ->union(function ($query) use ($userId) {
                    $query->select('to_user_id')
                        ->from('chats')
                        ->where('from_user_id', $userId)
                        ->distinct();
                });
        })
        ->where('id', '!=', $userId) // Exclude the current user
        ->whereNotIn('id', $group->users->pluck('id')->toArray()) // Exclude users already in the group
        ->get();

        return view('groups.show', compact('group', 'isMember', 'users'));
    }


    /*
        Store or Add users to my group(as an ADMIN ;)
        http://127.0.0.1:8000/groups/store
    */
    public function StoreUsersToGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
        ]);

        // Create the group with the name and user_id of the creator
        $group = Group::create([
            'name' => $request->name,
            'user_id' => Auth::id(), // Save the creator's user_id
        ]);

        // Sync the selected users and add the creator as a member
        $group->users()->sync($request->users);
        $group->users()->attach(Auth::id()); // Add the creator to the group members

        return redirect()->route('chat.index')->with('success', 'Group created successfully.');
    }

    /*
        Sending messages to group and replies
        http://127.0.0.1:8000/groups/{group}/send
        JSON--
    */
    public function SendGroupMessage(Request $request, Group $group)
    {
        $request->validate([
            'message' => 'required|string',
            'reply_to_message_id' => 'nullable|exists:group_messages,id',
        ]);

        GroupMessage::create([
            'group_id' => $group->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'reply_to_message_id' => $request->reply_to_message_id,
        ]);

        return response()->json(['success' => true]);
    }

    /*
        Fetch all users messages in the Group by its Id
        http://127.0.0.1:8000/groups/messages/{group}
        JSON--
    */
    public function FetchGroupMessages(Group $group)
    {
        // Fetch group messages with associated users and replies
        $messages = $group->messages()
            ->with(['user', 'repliedMessage'])
            ->get()
            ->map(function ($message) {
                // Add user name to message data
                $message->user_name = $message->user->name;
                $message->created_at_human = $message->created_at->diffForHumans();  // Pass human-readable time

                return $message;
            });

        return response()->json($messages);
    }

    /*
        Handle users to Join to the group
        http://127.0.0.1:8000/groups/{group}/join
    */
    public function join($id)
    {
        $group = Group::findOrFail($id);

        // Check if the group is open
        if ($group->open) {
            // Attach the authenticated user to the group
            $group->users()->attach(auth()->id());

            return redirect()->route('groups.show', $group->id)->with('success', 'You have joined the group.');
        }

        // If the group is closed, redirect back with a message
        return redirect()->route('groups.show', $group->id)->with('error', 'This group is closed. Please contact the admin to join.');
    }

    /*
        If users get bored from the group ,, they can just leave by that func
        http://127.0.0.1:8000/groups/{group}/leave
    */
    public function leaveGroup(Group $group)
    {
        $user = Auth::user();

        // Check if the user is the admin/creator
        if ($group->user_id == $user->id) {
            // If the user is the only member, delete the group
            if ($group->users()->count() == 1) {
                $group->delete();
                return redirect()->route('groups.index')->with('success', 'Group deleted successfully.');
            }

            // Reassign admin role to another member
            $newAdmin = $group->users()->where('user_id', '!=', $user->id)->first();
            if ($newAdmin) {
                $group->user_id = $newAdmin->id;
                $group->save();
            }
        }

        // Detach the user from the group
        $group->users()->detach($user->id);

        return redirect()->route('chat.index')->with('success', 'You have left the group.');
    }

    /*
        If ADMIN wants to make private group
        http://127.0.0.1:8000/groups/{group}/toggle
        JSON--
    */
    public function ToggleOpenGroup(Group $group)
    {
        // Ensure the authenticated user is the admin of the group
        if (Auth::id() !== $group->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to perform this action.',
            ], 403);
        }

        // Toggle the open status of the group
        $group->open = !$group->open;
        $group->save();

        // Return a JSON response with the new group status
        return response()->json([
            'success' => true,
            'group_open' => $group->open,
            'message' => 'Group status updated successfully.',
        ]);
    }


    /*
        To Handle with group messages deleteion (one by one)
        http://127.0.0.1:8000/groups/messages/{message}
        JSON--
    */
    public function DeleteMessageGroup($messageId)
    {
        // Find the message by ID
        $message = GroupMessage::findOrFail($messageId);

        // Ensure the user is authorized to delete the message
        if (Auth::id() !== $message->user_id && Auth::id() !== $message->group->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->delete();
        return response()->json(['success' => 'Message deleted']);
    }

    /*
        Only if ADMIN can change the covers and group pic
        http://127.0.0.1:8000/groups/{group}/update-image-covers
    */
    public function UpdateGroupPic(Request $request, Group $group)
    {
        // Ensure the authenticated user is the admin of the group
        if (Auth::id() !== $group->user_id) {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        }

        // Validate the request
        $request->validate([
            'group_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'group_cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'conditions' => 'nullable|string|max:255',
        ]);

        // Handle the group picture upload
        if ($request->hasFile('group_pic')) {
            $groupPicName = time().'_'.$request->group_pic->getClientOriginalName();
            $groupPicPath = $request->file('group_pic')->storeAs('group_pics', $groupPicName, 'public');

            // Delete the old picture if it's not the default
            if ($group->group_pic && $group->group_pic !== 'default_group_pic.png') {
                $oldPicPath = 'group_pics/' . $group->group_pic;
                if (Storage::disk('public')->exists($oldPicPath)) {
                    Storage::disk('public')->delete($oldPicPath);
                }
            }

            // Update the group picture path in the database
            $group->group_pic = $groupPicName;
        }

        // Handle the group cover upload
        if ($request->hasFile('group_cover')) {
            $groupCoverName = time().'_'.$request->group_cover->getClientOriginalName();
            $groupCoverPath = $request->file('group_cover')->storeAs('group_cover', $groupCoverName, 'public');

            // Delete the old cover if it's not the default
            if ($group->group_cover && $group->group_cover !== 'default_group_cover.png') {
                $oldCoverPath = 'group_cover/' . $group->group_cover;
                if (Storage::disk('public')->exists($oldCoverPath)) {
                    Storage::disk('public')->delete($oldCoverPath);
                }
            }

            // Update the group cover path in the database
            $group->group_cover = $groupCoverName;
        }

        // Update the group conditions
        if ($request->filled('conditions')) {
            $group->conditions = $request->conditions;
        }

        // Save the changes to the group
        $group->save();

        return redirect()->back()->with('success', 'Group images and conditions updated successfully.');
    }


    /*
        Only if ADMIN can Add users to his group even if its closed
        http://127.0.0.1:8000/groups/{group}/add-users
    */
    public function addUsers(Request $request, Group $group)
    {
        // Ensure the authenticated user is the admin of the group
        if (Auth::id() !== $group->user_id) {
            return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
        }

        // Validate the request
        $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
        ]);

        // Attach the selected users to the group
        $group->users()->syncWithoutDetaching($request->users);

        // Return a JSON response
        return response()->json(['message' => 'Users added successfully.'], 200);
    }

    /*
        Fetch data of the group Using its Id and get Infos about that group
        http://127.0.0.1:8000/group/details/{id}
        JSON--
    */
    public function getGroupDetails($id)
    {
        $group = Group::findOrFail($id);

        return response()->json([
            'group_pic' => $group->group_pic,
            'group_cover' => $group->group_cover,
        ]);
    }

    /*
        If users make ADMIN angry (he will close the chat group)
        http://127.0.0.1:8000/group/toggle-chat/{id}
        JSON--
    */
    public function toggleChat($id)
    {
        $group = Group::findOrFail($id);

        // Ensure the authenticated user is the admin of the group
        if (Auth::id() !== $group->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Toggle the chat_open status
        $group->chat_open = !$group->chat_open;
        $group->save();

        return response()->json(['success' => true, 'chat_open' => $group->chat_open]);
    }

    /*
        Send Recordings to Group chat
        http://127.0.0.1:8000/group/messages/upload-recording
        JSON--
    */
    public function uploadRecording(Request $request)
    {
        if ($request->hasFile('audio')) {
            $audioFile = $request->file('audio');
            $fileName = time() . '.' . $audioFile->getClientOriginalExtension();
            $filePath = $audioFile->storeAs('public/group_audio_msgs', $fileName); // Store file in the desired path

            // Create a new message with the audio path
            GroupMessage::create([
                'group_id' => $request->input('group_id'),
                'user_id' => Auth::id(), // Add the ID of the user uploading the audio
                'audio_path' => $fileName, // Store the relative path
            ]);

            return response()->json(['success' => true, 'path' => $filePath]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }

    /*
        Send Files, Images, Recorde_files to User chat
        http://127.0.0.1:8000/groups/{group}/upload
        JSON--
    */
    public function uploadFile(Request $request, Group $group)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,doc,docx,pdf,mp3|max:20480', // Validate file types and size
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('public/group_attachments', $fileName);

        // Save the file information in the GroupMessage table
        GroupMessage::create([
            'group_id' => $group->id,
            'user_id' => Auth::id(),
            'attachments' => $fileName,
        ]);

        return response()->json(['success' => true, 'path' => $filePath]);
    }

    /*
        Live searching for Groups
        Fetch all Groups which has same searchable item
        (search engine)
        JSON --
    */
    public function search(Request $request)
    {
        $query = $request->input('query');

        // Fetch groups that match the search query along with the creator (admin) details
        $groups = Group::with('creator')
                        ->where('name', 'like', '%' . $query . '%')
                        ->get();

        return response()->json([
            'groups' => $groups
        ]);
    }


}
