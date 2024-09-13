<!-- resources/views/groups/show.blade.php -->

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $group->name }} - Group Chat</title>

        <link rel="icon" type="image/jpg" href="{{ asset('storage/sys_images/icon.ico') }}"/>


        <!-- Styles -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/show_groups.css') }}">
    </head>
    <body>

        <div class="Main_container">
            <!-- Sidebar: User and Group List -->
            <div class="chatlist_container">
                <!-- Search bar and user/group list will be here -->
                @include('chat.chatslist') <!-- Include the infolist template -->

            </div>

            <!-- Chat Box: Group Messages -->
            <div class="Chat_box_container">
                <div class="Chat_box_header">
                    <button id="chat-list-btn">Chat List <i class="fa-solid fa-comments"></i></button>
                    <button id="info-btn">Info <i class="fa-solid fa-info-circle"></i></button>
                    <button onclick="window.location='{{ route('chat.index') }}'">Home <i class="fa-solid fa-home"></i></button>
                </div>


                @if ($isMember)
                    <!-- Chat Box: Group Messages -->
                    <div id="chat-box">
                        <!-- Messages will be dynamically loaded here -->
                    </div>

                    <!-- Chat Input Group -->
                    <div class="input-group mt-3">
                        <textarea id="message" class="form-control" placeholder="Type a message..."
                                {{ !$group->chat_open && Auth::id() !== $group->user_id ? 'disabled' : '' }}>
                        </textarea>

                        <input type="file" id="file-input" class="form-control" style="display:none;" />
                        <button id="files" class="btn" {{ !$group->chat_open && Auth::id() !== $group->user_id ? 'disabled' : '' }}>
                            <i class="fa-solid fa-plus"></i>
                        </button>

                        <button id="send" class="btn btn-success" {{ !$group->chat_open && Auth::id() !== $group->user_id ? 'disabled' : '' }}>
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>

                        <button id="record" class="btn" {{ !$group->chat_open && Auth::id() !== $group->user_id ? 'disabled' : '' }}>
                            <i class="fa-solid fa-microphone"></i>
                        </button>

                    </div>

                    <!-- Modal for Recordings -->
                    <div id="recordingModal" class="modal">
                        <div class="modal-content">
                            <span class="close" id="closeRecordingModal">&times;</span>
                            <div id="recordingsList"></div>
                        </div>
                    </div>
                    <!-- Modal for Uploading Files -->
                    <div id="fileModal" class="modal">
                        <div class="modal-content">
                            <span class="close" id="closeFileModal">&times;</span>
                            <div id="UploadingfilesList"></div>
                        </div>
                    </div>

                @else

                    <div class="join-group-container" style="color: #fff;">
                        @if ($group->open)
                            <!-- Join Group Form -->
                            <h1 style="text-align: center; padding: 20px; font-family: 'Arial', sans-serif; font-size: 24px; font-weight: 600; line-height: 1.4; letter-spacing: 0.5px; color: #535353;">
                                You are not a member of this group.
                                <form action="{{ route('groups.join', $group->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="Join_Group_btn">Join Group</button>
                                </form>
                            </h1>
                        @else
                            <h1 style="text-align: center; padding: 20px; font-family: 'Arial', sans-serif; font-size: 24px; font-weight: 600; line-height: 1.4; letter-spacing: 0.5px; color: #535353;">
                                This group is closed. You can ask the admin,
                                <a href="{{ url('chat/' . $group->user_id) }}" style="color: #007bff; text-decoration: none; font-weight: 700;">
                                    {{ $group->creator->name }}
                                </a> to join.
                            </h1>
                        @endif

                    </div>

                @endif
            </div>

            <!-- Sidebar: Group Info -->
            <div class="InfoList_container">
                <button id="close_infolist"><i class="fa-solid fa-arrow-left"></i> close Info's List</button>

                <div id="group-info">

                    <div class="profile-container">
                        <!-- Profile Cover with Change Button -->
                        <div class="cover-photo-container">
                            <img id="group_cover" src="{{ asset('default_group_cover.png') }}" alt="Cover Photo" class="cover-photo">
                            @if (Auth::id() == $group->user_id) <!-- Check if the user is the admin -->
                                <form action="{{ route('groups.updateImage', $group->id) }}" method="POST" enctype="multipart/form-data" class="cover-photo-form" id="coverPhotoForm">
                                    @csrf
                                    @method('PATCH')
                                    <label for="group_cover_input" class="change-cover-btn">
                                        <i class="fa-solid fa-camera"></i> Change Cover
                                    </label>
                                    <input type="file" name="group_cover" id="group_cover_input" class="form-control-file hidden-input" onchange="document.getElementById('coverPhotoForm').submit()">
                                    <button type="submit" class="d-none"></button>
                                </form>
                            @endif
                        </div>

                        <!-- Profile Picture with Change Button -->
                        <div class="profile-photo-container">
                            <img id="group_pic" src="{{ asset('default_group_pic.png') }}" alt="Profile Photo" class="profile-photo">
                            @if (Auth::id() == $group->user_id) <!-- Check if the user is the admin -->
                                <form action="{{ route('groups.updateImage', $group->id) }}" method="POST" enctype="multipart/form-data" class="profile-photo-form" id="profilePhotoForm">
                                    @csrf
                                    @method('PATCH')
                                    <label for="group_pic_input" class="change-profile-btn">
                                        <i class="fa-solid fa-camera"></i> Change Photo
                                    </label>
                                    <input type="file" name="group_pic" id="group_pic_input" class="form-control-file hidden-input" onchange="document.getElementById('profilePhotoForm').submit()">
                                    <button type="submit" class="d-none"></button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <h5 class="card-title" style="margin-top:60px">{{ $group->name }}</h5>
                    <p><strong>Admin </strong>
                        <a href="{{ url('chat/' . $group->user_id) }}" style="color: chartreuse">
                            {{ $group->creator->name }}
                        </a>
                    </p>

                    <p style="color: gray"><strong style="color: white">From_</strong> {{ $group->created_at->format('d M Y') }}</p>
                    <p><strong>Members</strong></p>
                    <div class="group-members-list">
                        @foreach($group->users as $user)
                            <a href="{{ url('chat/' . $user->id) }}">
                                {{ $user->name }},
                            </a>
                        @endforeach
                    </div>
                </div>

                @if (Auth::id() == $group->user_id) <!-- Check if the user is the admin -->

                    <form id="toggle-group-form" class="mt-3" method="POST" data-group-id="{{ $group->id }}">
                        @csrf
                        @method('PATCH')
                        <button type="button" id="toggle-group-btn" class="groups_toggle_btn">
                            @if ($group->open)
                                <div style="color: red">
                                    <i class="fa-solid fa-lock"></i> Switch to Private Group
                                </div>
                            @else
                                <div style="color: green">
                                    <i class="fa-solid fa-unlock"></i> Switch to Public Group
                                </div>
                            @endif
                        </button>
                    </form>

                    <button id="toggleChat" class="mt-3 toggle_Chat_btn">
                        @if ($group->chat_open)
                            <div style="color: red">
                                <i class="fa-solid fa-ban"></i> Close Chat
                            </div>
                        @else
                            <div style="color: green">
                                <i class="fa-solid fa-user-pen"></i> Open Chat
                            </div>
                        @endif
                    </button>


                    <form class="group_cover_form mt-3" action="{{ route('groups.updateImage', $group->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <!-- Group Conditions -->
                        <div class="group_cover_form_group">
                            <label for="conditions">Group Conditions</label>
                            <textarea name="conditions" id="conditions" class="conditions_form_control" rows="3">{{ old('conditions', $group->conditions) }}</textarea>
                        </div>
                        <button type="submit" class="Update_Images_btn">Save Changes</button>
                    </form>


                    <!-- Other group details -->
                    <hr>
                    <div>
                        <h1>Add Users to <span style="color:#FFA500">{{ $group->name }}</span></h1>

                        <form id="addUsersForm">
                            @csrf
                            <!-- List of checkboxes for users -->
                            <div class="form-group">
                                <label><p>easy access</p></label>
                                @if($users->isEmpty())
                                    <p>No users To add &#128549;</p>
                                @else
                                    @foreach($users as $user)
                                        <div class="form-check">
                                            <input type="checkbox" name="users[]" value="{{ $user->id }}" id="user-{{ $user->id }}" class="form-check-input">
                                            <label for="user-{{ $user->id }}" class="form-check-label">
                                                {{ $user->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <!-- Add Users button will only be visible if there are users -->
                            @if(!$users->isEmpty())
                                <button type="submit" class="Add_Users_btn">Add Marked Users</button>
                            @endif
                        </form>


                    </div>

                @endif

                @if ($group->open)
                    @if ($isMember)
                        <h5>Group Conditions</h5>
                        <p>{{ $group->conditions }}</p>

                        <hr style="background-color: white">
                        <form action="{{ route('groups.leave', $group->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="Leave_Group_btn">Leave Group</button>
                        </form>

                    @else
                        <h5>Group Conditions</h5>
                        <p>{{ $group->conditions }}</p>

                        <hr style="background-color: white">
                        <form action="{{ route('groups.join', $group->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="Join_Group_btn">Join Group</button>
                        </form>

                    @endif
                @else
                    @if ($isMember)

                        <h5>Group Conditions</h5>
                        <p>{{ $group->conditions }}</p>

                        <hr style="background-color: white">
                        <form action="{{ route('groups.leave', $group->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="Leave_Group_btn">Leave Group</button>
                        </form>


                    @else
                        <h1 style="text-align: center;">This group is closed. You can ask the admin,
                            <a href="{{ url('chat/' . $group->user_id) }}">
                                {{ $group->creator->name }}
                            </a>, to join.
                        </h1>
                    @endif
                @endif

            </div>

        </div>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        {{--Fectch selected group cover and pic--}}
        <script>
            $(document).ready(function() {
                // Function to extract the group ID from the URL
                function getGroupIdFromUrl() {
                    const pathArray = window.location.pathname.split('/');
                    return pathArray[pathArray.length - 1]; // Get the last part of the URL
                }

                // Function to load group info using the extracted group ID
                function loadGroupInfo(groupId) {
                    $.get(`/group/details/${groupId}`, function(data) {
                        // Update the group and cover photos
                        $('#group_pic').attr('src', data.group_pic ? `/storage/group_pics/${data.group_pic}` : '{{ asset('default-profile.jpg') }}');
                        $('#group_cover').attr('src', data.group_cover ? `/storage/group_cover/${data.group_cover}` : '{{ asset('default-cover.jpg') }}');
                    });
                }

                // Get the group ID and load the group info
                const groupId = getGroupIdFromUrl();
                loadGroupInfo(groupId);
            });
        </script>

        {{--toggle group statue from private to public--}}
        <script>
            $(document).ready(function () {
                $('#toggle-group-btn').click(function () {
                    var groupId = $('#toggle-group-form').data('group-id');
                    var token = $('input[name=_token]').val();
                    var method = $('input[name=_method]').val();

                    $.ajax({
                        url: '/groups/' + groupId + '/toggle',
                        type: 'PATCH',
                        data: {
                            _token: token,
                            _method: method
                        },
                        success: function (response) {
                            if (response.success) {
                                // Update the button text and icon based on the new group status

                                if (response.group_open) {
                                    $('#toggle-group-btn').html(`<div style="color: red"><i class="fa-solid fa-lock"></i> Switch to Private Group</div>`);
                                } else {
                                    $('#toggle-group-btn').html(`<div style="color: green"><i class="fa-solid fa-unlock"></i> Switch to Public Group</div>`);
                                }
                            }
                            else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function (xhr) {
                            alert('An error occurred. Please try again.');
                        }
                    });
                });
            });
        </script>

        {{--Add users to Admin's Group!--}}
        <script>
            $(document).ready(function() {
                $('#addUsersForm').on('submit', function(e) {
                    e.preventDefault(); // Prevent the default form submission

                    // Collect checked user IDs
                    let selectedUsers = [];
                    $('input[name="users[]"]:checked').each(function() {
                        selectedUsers.push($(this).val());
                    });

                    // Send the AJAX request
                    $.ajax({
                        url: '{{ route('groups.addUsers', $group->id) }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            users: selectedUsers,
                        },
                        dataType: 'json',
                        success: function(response) {
                            // Handle success response
                            alert(response.message); // Or display a success message in the UI
                        },
                        error: function(xhr) {
                            // Handle error response
                            alert(xhr.responseJSON.message); // Show error message
                        }
                    });
                });
            });
        </script>

        {{-- Controlling the Modals--}}
        <script>
            $(document).ready(function() {
                // Function to show modal with content
                function showModal(modalId) {
                    $(modalId).fadeIn();
                }

                // Function to hide modal
                function hideModal(modalId) {
                    $(modalId).fadeOut();
                }

                // Event handler to open the recordings modal when recordings are available
                function handleRecordingModal() {
                    if ($('#recordingsList').children().length > 0) {
                        showModal('#recordingModal');
                    }
                }

                // Event handler to open the file modal when files are available
                function handleFileModal() {
                    if ($('#UploadingfilesList').children().length > 0) {
                        showModal('#fileModal');
                    }
                }

                // Click event to close the recordings modal
                $('#closeRecordingModal').on('click', function() {
                    hideModal('#recordingModal');
                });

                // Click event to close the files modal
                $('#closeFileModal').on('click', function() {
                    hideModal('#fileModal');
                });

                // Close modals if clicked outside of the modal content
                $(window).on('click', function(event) {
                    if ($(event.target).is('.modal')) {
                        hideModal('.modal');
                    }
                });

                // Call handle functions after adding content dynamically
                function updateRecordingsAndFiles() {
                    handleRecordingModal();
                    handleFileModal();
                }

                // Call update function after content is dynamically added
                $('#recordingsList').on('DOMNodeInserted', updateRecordingsAndFiles);
                $('#UploadingfilesList').on('DOMNodeInserted', updateRecordingsAndFiles);
            });
        </script>

        {{-- Controlling the Group permissions--}}
        <script>
            $(document).ready(function() {
                // Function to extract the group ID from the URL
                function getGroupIdFromUrl() {
                    const pathArray = window.location.pathname.split('/');
                    return pathArray[pathArray.length - 1]; // Get the last part of the URL
                }

                // Get the CSRF token from the meta tag
                const csrfToken = $('meta[name="csrf-token"]').attr('content');

                // Function to toggle chat open/close status
                function toggleChatStatus(groupId) {
                    $.ajax({
                        url: `/group/toggle-chat/${groupId}`,
                        type: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            if (response.success) {
                                const chatOpen = response.chat_open;

                                // Update the button text and icon
                                $('#toggleChat').html(chatOpen ?
                                    `<div style="color: red">
                                        <i class="fa-solid fa-ban"></i> Close Chat
                                    </div>` :
                                    `<div style="color: green">
                                        <i class="fa-solid fa-user-pen"></i> Open Chat
                                    </div>`);

                                // Optionally, update the input/button states based on chat status
                                $('#message').prop('disabled', !chatOpen);
                                $('#send').prop('disabled', !chatOpen);
                            } else {
                                alert('Error toggling chat status');
                            }
                        },
                        error: function() {
                            alert('An error occurred. Please try again.');
                        }
                    });
                }

                // Get the group ID and set up the toggle button click event
                const groupId = getGroupIdFromUrl();
                $('#toggleChat').on('click', function() {
                    toggleChatStatus(groupId);
                });
            });

        </script>

        {{-- Controlling the chat + infos lists sliders--}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Get the elements
                const chatListBtn = document.getElementById('chat-list-btn');
                const infoBtn = document.getElementById('info-btn');
                const infoListContainer = document.getElementById('infoList');
                const chatListContainer = document.querySelector('.chatlist_container');
                const closeBtn = document.getElementById('close_infolist');

                // Flag to track the state of the Info List
                let isInfoListOpen = false;
                let isChatListOpen = false;

                // Function to toggle Info List visibility
                function toggleInfoList() {
                    if (isInfoListOpen) {
                        infoListContainer.classList.remove('active'); // Slide out
                    } else {
                        infoListContainer.classList.add('active'); // Slide in
                    }
                    isInfoListOpen = !isInfoListOpen; // Toggle the state
                }

                // Function to toggle Chat List visibility
                function toggleChatList() {
                    if (isChatListOpen) {
                        chatListContainer.classList.remove('active'); // Slide out
                    } else {
                        chatListContainer.classList.add('active'); // Slide in
                    }
                    isChatListOpen = !isChatListOpen; // Toggle the state
                }

                // Add event listener for opening/closing the info list with the button
                infoBtn.addEventListener('click', function() {
                    toggleInfoList();
                });

                // Add event listener for opening/closing the chat list with the button
                chatListBtn.addEventListener('click', function() {
                    toggleChatList();
                });

                // Add event listener for closing the info list with the close button
                closeBtn.addEventListener('click', function() {
                    toggleInfoList();
                });

                // Close the info list if clicked outside of the container
                window.addEventListener('click', function(event) {
                    if (isInfoListOpen && !infoListContainer.contains(event.target) && event.target !== infoBtn) {
                        toggleInfoList();
                    }
                    if (isChatListOpen && !chatListContainer.contains(event.target) && event.target !== chatListBtn) {
                        toggleChatList();
                    }
                });
            });
        </script>

        {{-- Controlling the Infos list sliders--}}
        <script>
            $(document).ready(function() {
                // Function to toggle the info list container
                $('#info-btn').on('click', function() {
                    if ($('.InfoList_container').hasClass('active')) {
                        $('.InfoList_container').removeClass('active'); // Slide out the info list container
                    } else {
                        $('.InfoList_container').addClass('active'); // Slide in the info list container
                    }
                });

                // Function to close the info list container when the close button is clicked
                $('#close_infolist').on('click', function() {
                    $('.InfoList_container').removeClass('active'); // Slide out the info list container
                });

                // Detect clicks outside the info list container to close it
                $(document).on('click', function(event) {
                    // Check if the click is outside the info list container and the info button
                    if (!$(event.target).closest('.InfoList_container, #info-btn').length) {
                        $('.InfoList_container').removeClass('active'); // Slide out the info list container
                    }
                });
            });
        </script>

        {{-- upload and send Recordings--}}
        <script>
            $(document).ready(function() {
                let mediaRecorder;
                let recordedChunks = [];
                let mediaStream;
                let isRecording = false;

                // Setup media recording
                function setupMediaRecorder() {
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({ audio: true })
                            .then(function(stream) {
                                mediaStream = stream;
                                mediaRecorder = new MediaRecorder(stream);

                                mediaRecorder.ondataavailable = function(event) {
                                    if (event.data.size > 0) {
                                        recordedChunks.push(event.data);
                                    }
                                };

                                mediaRecorder.onstop = function() {
                                    if (mediaStream) {
                                        mediaStream.getTracks().forEach(track => track.stop());
                                        mediaStream = null;
                                    }

                                    const blob = new Blob(recordedChunks, { type: 'audio/wav' });
                                    recordedChunks = [];

                                    const audioUrl = URL.createObjectURL(blob);
                                    const audio = document.createElement('audio');
                                    audio.controls = true;
                                    audio.src = audioUrl;

                                    $('#recordingsList').empty().append(audio).append(`
                                        <div class="cont_btns">
                                            <button id="sendRecording" class="btn_send"><i class="fas fa-paper-plane"></i></button>
                                            <button id="deleteRecording" class="btn_delete"><i class="fas fa-trash"></i></button>
                                        </div>
                                    `);

                                    // Open the modal after preparing the recording
                                    $('#recordingModal').fadeIn();

                                    $('#sendRecording').on('click', function() {
                                        uploadRecording(blob);
                                    });

                                    $('#deleteRecording').on('click', function() {
                                        $('#recordingsList').empty();
                                        $('#recordingModal').fadeOut(); // Close modal after deleting
                                    });

                                    $('#record-icon').removeClass('fa-pause').addClass('fa-microphone');
                                    isRecording = false;
                                };

                                mediaRecorder.start();
                                isRecording = true;
                                $('#record-icon').removeClass('fa-microphone').addClass('fa-pause');
                            })
                            .catch(function(error) {
                                console.error('Error accessing the microphone:', error);
                            });
                    }
                }

                $('#record').on('click', function() {
                    if (isRecording) {
                        if (mediaRecorder) {
                            mediaRecorder.stop();
                        }
                    } else {
                        setupMediaRecorder();
                    }
                });

                function uploadRecording(blob) {
                    const formData = new FormData();
                    formData.append('audio', blob, 'recording.wav'); // Append the blob with correct name
                    formData.append('_token', '{{ csrf_token() }}'); // Add CSRF token
                    formData.append('group_id', '{{ $group->id }}'); // Ensure `group_id` is added

                    $.ajax({
                        url: '{{ route("groupMessages.uploadRecording") }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#recordingsList').empty(); // Clear the recording after sending
                            $('#recordingModal').fadeOut(); // Close modal after sending
                            fetchMessages(); // Refresh messages to show the newly sent recording
                        },
                        error: function(xhr) {
                            console.error('Error uploading recording:', xhr.responseText);
                        }
                    });
                }




                // Initialize recording state
                $('#record-icon').addClass('fa-microphone');
            });
        </script>

        {{-- upload and send all kind of files ( 40 MG >msg)--}}
        <script>
            $(document).ready(function() {
                $('#files').on('click', function() {
                    $('#file-input').click(); // Trigger file input click
                });

                $('#file-input').on('change', function() {
                    let file = this.files[0];
                    if (file) {
                        displayFile(file);
                    }
                });

                function displayFile(file) {
                    const fileUrl = URL.createObjectURL(file);
                    const fileName = file.name;
                    const fileType = file.type;

                    let content = '';

                    // Check file type and display accordingly
                    if (fileType.startsWith('image/')) {
                        content = `<img src="${fileUrl}" alt="${fileName}" class="uploaded-image" />`;
                    } else if (fileType === 'application/pdf' || fileType.startsWith('application/vnd.openxmlformats-officedocument')) {
                        content = `
                            <a href="${fileUrl}" download="${fileName}" class="btn btn-primary">Download ${fileName}</a>
                        `;
                    } else {
                        content = `
                            <a href="${fileUrl}" download="${fileName}">${fileName}</a>
                        `;
                    }

                    $('#UploadingfilesList').empty().append(`
                        <div class="upload-item">
                            ${content}
                            <div class="cont_btns">
                                <button id="sendFile" class="btn_send"><i class="fas fa-paper-plane"></i></button>
                                <button id="deleteFile" class="btn_delete"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    `);

                    // Open the modal after preparing the file
                    $('#fileModal').fadeIn();

                    $('#sendFile').on('click', function() {
                        uploadFile(file);
                    });

                    $('#deleteFile').on('click', function() {
                        $('#UploadingfilesList').empty();
                        $('#fileModal').fadeOut(); // Close modal after deleting
                        URL.revokeObjectURL(fileUrl); // Release the object URL when done
                    });
                }


                                function uploadFile(file) {
                    const groupId = '{{ $group->id }}'; // Make sure this is set properly in the Blade template
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('_token', '{{ csrf_token() }}'); // Add CSRF token

                    $.ajax({
                        url: `{{ route('groups.uploadFile', ':group') }}`.replace(':group', groupId), // Include group ID in URL
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#UploadingfilesList').empty(); // Clear the file display after sending
                            $('#fileModal').fadeOut(); // Close modal after sending
                            fetchMessages(); // Refresh messages to show the newly sent file
                        },
                        error: function(xhr) {
                            console.error('Error uploading file:', xhr.responseText);
                        }
                    });
                }

            });
        </script>

        {{--fetching, controlling, handling, view, all messages--}}
        <script>
            $(document).ready(function () {
                const groupId = '{{ $group->id }}'; // The ID of the group chat
                const chatBox = $('#chat-box'); // The chat box container
                const messageInput = $('#message'); // The message input textarea
                const userId = {{ Auth::id() }}; // The ID of the current user
                let lastMessageId = null; // To keep track of the last message ID fetched
                let replyToMessageId = null; // To store the ID of the message being replied to

                // Function to escape HTML to prevent code injection
                function escapeHtml(unsafe) {
                    return unsafe
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
                }

                // Function to fetch messages from the server
                function fetchMessages() {
                    $.get(`/groups/messages/${groupId}`, function (data) {
                        // Create a map of messages by their ID for quick lookup
                        const messageMap = new Map();
                        data.forEach(message => {
                            messageMap.set(message.id, message);
                        });

                        // Extract the IDs of the fetched messages
                        const fetchedMessageIds = Array.from(messageMap.keys());

                        // Compare with existing messages to detect deletions
                        const currentMessageIds = chatBox.find('.message').map(function () {
                            return $(this).data('message-id');
                        }).get();

                        // Detect deleted messages
                        currentMessageIds.forEach(id => {
                            if (!fetchedMessageIds.includes(id)) {
                                // Message ID is no longer in the fetched data, so it was deleted
                                chatBox.find(`.message[data-message-id="${id}"]`).remove();
                            }
                        });

                        // Append new messages
                        let newMessages = data.filter(message => !lastMessageId || message.id > lastMessageId);
                        if (newMessages.length > 0) {
                            appendMessages(newMessages);
                            lastMessageId = newMessages[newMessages.length - 1].id;
                        }

                        // Re-initiate long-polling
                        pollForNewMessages();
                    });
                }

                // Function to append new messages to the chat box
                function appendMessages(messages) {
                    messages.forEach(function (message) {
                        const isAdmin = {{ Auth::id() == $group->user_id ? 'true' : 'false' }};
                        const isSent = message.user_id === userId;
                        const messageClass = isSent ? 'sent' : 'received';

                        // Fetch the user name based on user_id (assuming `users` is a map of user ID to user name)
                        const userName = message.user_name || 'Unknown'; // Replace with actual logic to fetch user name

                        let content = '';

                        // Check if the message is a reply to another message
                        if (message.reply_to_message_id) {
                            const repliedMessage = messages.find(m => m.id === message.reply_to_message_id);

                            if (repliedMessage) {
                                let repliedContent = '';

                                // Handle the replied message attachments
                                if (repliedMessage.attachments) {
                                    const fileUrl = `/storage/group_attachments/${repliedMessage.attachments}`;
                                    const fileType = repliedMessage.attachments.split('.').pop().toLowerCase();

                                    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
                                        repliedContent = `<div class="replied-image"><strong>Replying to:</strong> <img src="${fileUrl}" alt="${repliedMessage.attachments}" style="max-width: 50px; height: auto; cursor: pointer;" /></div>`;
                                    } else {
                                        repliedContent = `<div class="replied-image"><strong>Replying to:</strong><a href="${fileUrl}" download>${repliedMessage.attachments}</a></div>`;
                                    }
                                } else {
                                    repliedContent = `<div class="replied-image"><strong>Replying to:</strong>` + escapeHtml(repliedMessage.message).replace(/\n/g, '<br>') + `</div>`;
                                }

                                content += `<div class="replied-message">${repliedContent}</div>`;
                            }
                        }

                        if (message.message !== null) {
                            content += escapeHtml(message.message).replace(/\n/g, '<br>');
                        }

                        if (message.audio_path !== null) {
                            content += `<audio controls src="/storage/group_audio_msgs/${message.audio_path}"></audio>`;
                        }

                        if (message.attachments !== null) {
                            const fileUrl = `/storage/group_attachments/${message.attachments}`;
                            const fileName = message.attachments;
                            const fileType = message.attachments.split('.').pop().toLowerCase();

                            if (fileType === 'pdf' || fileType === 'doc' || fileType === 'docx') {
                                content += `<a href="${fileUrl}" download class="btn btn-primary">Download ${fileName}</a>`;
                            } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
                                content += `<img src="${fileUrl}" alt="${fileName}" style="max-width: 100%; max-height: 500px;" />`;
                            } else {
                                content += `<a href="${fileUrl}" download>${fileName}</a>`;
                            }
                        }
                        const timestamp = message.created_at_human; // Use the human-readable time from backend
                        const deleteButton = (isAdmin || isSent) ? `<button class="delete_msg_btn" data-message-id="${message.id}"><i class="fa-solid fa-trash"></i></button>` : '';

                        const replyButton = `<button class="reply_msg_btn" data-message-id="${message.id}"><i class="fa-solid fa-reply"></i> Reply </button>`;

                        chatBox.append(`
                            <div class="message ${messageClass}" data-message-id="${message.id}">
                                <div class="message-header">
                                    <strong>${userName}</strong> <span class="message-time">${timestamp}</span>
                                </div>
                                <div class="message-content">
                                    ${content}
                                </div>
                                <div class="message-footer">
                                    ${deleteButton}
                                    ${replyButton}
                                </div>
                            </div>
                        `);
                    });

                    setTimeout(() => {
                        chatBox.scrollTop(chatBox[0].scrollHeight);
                    }, 1000); // Delay to ensure all messages are rendered
                }

                // Handle clicking the reply button
                chatBox.on('click', '.reply_msg_btn', function () {
                    replyToMessageId = $(this).data('message-id');
                    const originalMessageContent = $(`.message[data-message-id="${replyToMessageId}"] .message-content`).html();
                    messageInput.focus();
                });

                // Function to send a message with a reply reference
                function sendMessage() {
                    const message = messageInput.val();
                    if (message) {
                        $.post('{{ route("groups.sendMessage", $group->id) }}', {
                            group_id: groupId,
                            message: message,
                            reply_to_message_id: replyToMessageId, // Include the reply_to_message_id
                            _token: '{{ csrf_token() }}'
                        }, function (data) {
                            messageInput.val(''); // Clear the input field
                            replyToMessageId = null; // Clear the reply message id
                        });
                    }
                }

                // Function to delete a message
                function deleteMessage(messageId) {
                    $.ajax({
                        url: `/groups/messages/${messageId}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function () {
                            fetchMessages(); // Reload messages to reflect deletion
                        }
                    });
                }

                // Handle keydown events in the textarea
                messageInput.on('keydown', function (event) {
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault(); // Prevent the default new line behavior
                        sendMessage(); // Trigger send message
                    }
                });

                // Handle clicking the "Send" button
                $('#send').click(function () {
                    sendMessage();
                });

                // Handle clicking the delete button
                chatBox.on('click', '.delete_msg_btn', function () {
                    const messageId = $(this).data('message-id');
                    deleteMessage(messageId);
                });

                // Poll for new messages every 1 second
                function pollForNewMessages() {
                    setTimeout(fetchMessages, 1000); // Adjust the delay as needed
                }

                // Start fetching messages and initiate long-polling
                fetchMessages();
            });
        </script>

    </body>
</html>
