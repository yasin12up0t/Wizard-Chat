<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class ChatController extends Controller
{
    /*
        View Main Page for Chating
        Fetch all users who have had a conversation with the authenticated user
        http://127.0.0.1:8000/chat
        or
        http://127.0.0.1:8000/
    */
    public function Viewindex()
    {
        $userId = Auth::id();

        // Fetch groups with the creator (admin) details
        $groups = Group::with('creator')->get(); // Eager load creator relationship

        // Get the authenticated user's information
        $authUser = User::find($userId);

        // Get the list of users who have had a conversation with the authenticated user
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

        // Pass both users and the authenticated user's information to the view
        return view('chat.index', compact('users', 'authUser', 'groups'));
    }


    /*
        Live searching for users
        Fetch all users who have had a conversation with the authenticated user
        (search engine)
        JSON --
    */
    public function searchUsers(Request $request)
    {
        $query = $request->get('query');
        $users = User::where('name', 'like', '%' . $query . '%')
            ->where('id', '!=', Auth::id())
            ->get();

        return response()->json($users);
    }

    /*
        View Main Page for Chating
        Show the conversation between the authenticated user and the selected user
        http://127.0.0.1:8000/chat/{userid}
    */
    public function SelectUserChat(User $user)
    {
        $messages = Chat::where(function($query) use ($user) {
                $query->where('from_user_id', Auth::id())->where('to_user_id', $user->id);
            })
            ->orWhere(function($query) use ($user) {
                $query->where('from_user_id', $user->id)->where('to_user_id', Auth::id());
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Assuming you want to pass the user's ID or other unique identifier
        $conversationId = $user->id;

        return view('chat.show', compact('user', 'messages', 'conversationId'));
    }

    /*
        View Main Page for Chating
        Fetch the conversation messages between the authenticated user and the selected user
        JSON --
    */
    public function fetchChatMessages($userId)
    {
        $messages = Chat::where(function($query) use ($userId) {
                $query->where('from_user_id', Auth::id())->where('to_user_id', $userId);
            })
            ->orWhere(function($query) use ($userId) {
                $query->where('from_user_id', $userId)->where('to_user_id', Auth::id());
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Format the messages with the timestamp
        $formattedMessages = $messages->map(function($message) {
            return [
                'id' => $message->id,
                'from_user_id' => $message->from_user_id,
                'to_user_id' => $message->to_user_id,
                'message' => $message->message,
                'audio_path' => $message->audio_path,
                'attachments' => $message->attachments,
                'created_at' => $message->created_at->diffForHumans(),

                'seen' => $message->seen
            ];
        });

        // Mark messages as seen when the recipient fetches them
        $messageIds = $messages->pluck('id')->toArray(); // Convert collection to array
        if (!empty($messageIds)) {
            $this->markMessagesAsSeen($messageIds); // Mark the messages as seen
        }

        return response()->json($formattedMessages);
    }
    /*
        Mark specified messages as seen
        that function will be associated with fetchChatMessages()
    */
    public function markMessagesAsSeen(array $messageIds)
    {
        $userId = Auth::id();

        // Update messages to seen status where the authenticated user is the recipient
        Chat::whereIn('id', $messageIds)
            ->where('to_user_id', $userId) // Only mark messages as seen if the authenticated user is the recipient
            ->update(['seen' => true]);
    }

    /*
        View Main Page for Chating
        Send a new message from the authenticated user to the selected user
        http://127.0.0.1:8000/chat/send
        JSON --
    */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'reply_to_message_id' => 'nullable|exists:chats,id',
        ]);

        $message = Chat::create([
            'from_user_id' => Auth::id(),
            'to_user_id' => $request->to_user_id,
            'message' => $request->message,
            'reply_to_message_id' => $request->reply_to_message_id,
            'seen' => false,
        ]);

        return response()->json($message);
    }


    /*
        View Main Page for Chating
        Fetch all users who have had a conversation with the authenticated user
        View them in the ChatList view
        http://127.0.0.1:8000/conversation-users
        JSON --
    */
    public function getConversationUsers()
    {
        $currentUserId = Auth::id();

        // Fetch users that have a conversation with the current user (either as sender or receiver)
        $users = User::whereIn('id', function ($query) use ($currentUserId) {
            $query->select('from_user_id')
                ->from('chats')
                ->where('to_user_id', $currentUserId)
                ->union(
                    Chat::select('to_user_id')
                        ->where('from_user_id', $currentUserId)
                );
        })->get();

        $conversationData = $users->map(function ($user) use ($currentUserId) {
            // Fetch the last message and unseen messages count for each conversation
            $lastMessage = Chat::where(function ($query) use ($user, $currentUserId) {
                $query->where('from_user_id', $currentUserId)
                    ->where('to_user_id', $user->id);
            })
            ->orWhere(function ($query) use ($user, $currentUserId) {
                $query->where('from_user_id', $user->id)
                    ->where('to_user_id', $currentUserId);
            })
            ->orderBy('created_at', 'desc')
            ->first();

            $unseenCount = Chat::where('from_user_id', $user->id)
                            ->where('to_user_id', $currentUserId)
                            ->where('seen', false)
                            ->count();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'profile_pic' => $user->profile_pic,
                'last_message' => $lastMessage ? $lastMessage->message : '',
                'last_message_date' => $lastMessage ? $lastMessage->created_at->diffForHumans() : 'No messages yet',
                'unseen_count' => $unseenCount
            ];
        });

        return response()->json($conversationData);
    }

    /*
        View Main Page for Chating
        Fetch all user infos like name, email, bio ....etc
        View them in the InfoList view
        http://127.0.0.1:8000/user/details/{id}
        JSON --
    */
    public function getUserDetails($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /*
        Delete Selected User Conversation (all messages)
        http://127.0.0.1:8000/chat/{conversationId}
    */
    public function deleteConversation($conversationId)
    {
        $userId = Auth::id();

        // Delete all chats involving the authenticated user and the specified user
        Chat::where(function($query) use ($userId, $conversationId) {
                $query->where('from_user_id', $userId)
                    ->where('to_user_id', $conversationId);
            })
            ->orWhere(function($query) use ($userId, $conversationId) {
                $query->where('from_user_id', $conversationId)
                    ->where('to_user_id', $userId);
            })
            ->delete();

        return redirect()->route('chat.index');
    }

    /*
        Delete Selected Message in Chatting (Specefied message)
        http://127.0.0.1:8000/chat/messages/{message}
        JSON --
    */
    public function deleteMessage($messageId)
    {
        $message = Chat::findOrFail($messageId);

        // Check if the user is authorized to delete the message
        if ($message->from_user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json(['success' => true]);
    }

    /*
        UpdateUserProfile
        http://127.0.0.1:8000/profile/update
    */
    public function UpdateUserProfile(Request $request)
    {
        // Validate input
        $request->validate([
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'profile_cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'nullable|max:24',
            'bio' => 'nullable|max:100',
            'gender' => 'nullable|max:10',
            'phone' => 'nullable|max:15',
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Update name if provided
        if ($request->has('name')) {
            $user->name = $request->input('name');
        }

        // Update bio if provided
        if ($request->has('bio')) {
            $user->bio = $request->input('bio');
        }

        // Update gender if provided
        if ($request->has('gender')) {
            $user->gender = $request->input('gender');
        }

        // Update phone if provided
        if ($request->has('phone')) {
            $user->phone = $request->input('phone');
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_pic')) {
            // Generate a new file name
            $file = $request->file('profile_pic');
            $fileName = $user->id . '_' . strtolower(str_replace(' ', '_', $user->name)) . '_profile.' . $file->getClientOriginalExtension();

            // Delete the old profile picture if it's not the default
            if ($user->profile_pic && $user->profile_pic !== 'default_profile_pic.png') {
                $oldPicPath = 'profile_pics/' . $user->profile_pic;
                if (Storage::disk('public')->exists($oldPicPath)) {
                    Storage::disk('public')->delete($oldPicPath);
                }
            }

            // Store the new profile picture
            $file->storeAs('profile_pics', $fileName, 'public');
            $user->profile_pic = $fileName;
        }

        // Handle cover photo upload
        if ($request->hasFile('profile_cover')) {
            // Generate a new file name
            $file = $request->file('profile_cover');
            $fileName = $user->id . '_' . strtolower(str_replace(' ', '_', $user->name)) . '_cover.' . $file->getClientOriginalExtension();

            // Delete the old cover photo if it's not the default
            if ($user->profile_cover && $user->profile_cover !== 'default_profile_cover.png') {
                $oldCoverPath = 'profile_cover/' . $user->profile_cover;
                if (Storage::disk('public')->exists($oldCoverPath)) {
                    Storage::disk('public')->delete($oldCoverPath);
                }
            }

            // Store the new cover photo
            $file->storeAs('profile_cover', $fileName, 'public');
            $user->profile_cover = $fileName;
        }

        // Save the updated user information
        $user->save();

        // Return back with a success message
        return back()->with('success', 'Profile updated successfully!');
    }


    /*
        Send Recordings to User chat
        http://127.0.0.1:8000/send-recording
        JSON --
    */
    public function uploadRecording(Request $request)
    {
        // Handle the audio file upload
        if ($request->hasFile('audio')) {
            $audioFile = $request->file('audio');
            $fileName = time() . '.' . $audioFile->getClientOriginalExtension();
            $filePath = $audioFile->storeAs('public/audio_msgs', $fileName); // Store in public/audio_msgs directory


            // Save the message with the audio file path
            $message = Chat::create([
                'from_user_id' => Auth::id(),
                'to_user_id' => $request->to_user_id,
                'audio_path' => $fileName, // Store relative path
                'seen' => false, // Initialize `seen` status as false
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    /*
        Send Files, Images, Recorde_files to User chat
        http://127.0.0.1:8000/upload-file
        JSON --
    */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:40960', // max:40MB (40MB = 40960KB)
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('attachments', 'public');

            $chat = new Chat();
            $chat->from_user_id = Auth::id();
            $chat->to_user_id = $request->input('to_user_id');
            $chat->attachments = $path;
            $chat->save();

            return response()->json(['message' => 'File uploaded successfully.']);
        }

        return response()->json(['message' => 'No file uploaded.'], 400);
    }

}
