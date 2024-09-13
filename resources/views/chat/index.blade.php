<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>chats</title>

        <link rel="stylesheet" href="{{ asset('css/index_chat.css') }}">
        <link rel="icon" type="image/jpg" href="{{ asset('storage/sys_images/icon.ico') }}"/>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    </head>
    <body>

        <div class="Main_container">

            <div class="chatlist_container">
                <button class="open-chat-box">Account dashbaord <i class="fa-solid fa-arrow-right"></i></button>

                @include('chat.chatslist') <!-- Include the infolist template -->

            </div>

            <div class="Chat_box_container">
                <button class="close-chat-box"><i class="fa-solid fa-arrow-left"></i> Back to ChatList</button>

                <div class="Chat_box_insider_container">
                    {{--the form for your profile --}}
                    <div class="Profile_Update_container">

                        <div class="profile-cover-container">
                            <!-- Cover Image -->
                            <div class="coverpic_container">
                                <img src="{{ asset('storage/profile_cover/' .  Auth::user()->profile_cover) }}" alt="Profile Cover" class="cover-photo">
                                <button class="change-cover-btn" onclick="document.getElementById('profile_cover').click();"><i class="fa-solid fa-image"></i></button>
                            </div>

                            <!-- Profile Picture -->
                            <div class="profilepic_container">
                                <img src="{{ asset('storage/profile_pics/' .  Auth::user()->profile_pic)}}" alt="Profile Picture" class="profile-pic">
                                <button class="change-profile-btn" onclick="document.getElementById('profile-pic').click();"><i class="fa-regular fa-image"></i></button>
                            </div>
                        </div>

                        <!-- Form for Uploading Images -->
                        <form class="image_upload_form" action="{{ route('Chatprofile.update') }}" method="POST" enctype="multipart/form-data" id="imageForm">
                            @csrf
                            <!-- Hidden inputs for file uploads -->
                            <input type="file" class="d-none" id="profile-pic" name="profile_pic" accept="image/*" onchange="submitImageForm()">
                            <input type="file" class="d-none" id="profile_cover" name="profile_cover" accept="image/*" onchange="submitImageForm()">
                        </form>

                        <!-- Form for Updating Bio, Phone, and Gender -->
                        <form class="user_info_form" action="{{ route('Chatprofile.update') }}" method="POST" id="detailsForm">
                            @csrf

                            <div class="form-group">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ Auth::user()->name }}">
                            </div>

                            <div class="form-group">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4">{{ Auth::user()->bio }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="{{ Auth::user()->phone }}">
                            </div>

                            @if (Auth::user()->gender === null)
                                <div class="form-group">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-control" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ Auth::user()->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ Auth::user()->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                            @endif

                            <!-- Submit Button for Bio, Phone, and Gender -->
                            <button type="submit" class="btn btn-primary">Update Profile Details</button>
                        </form>

                        <!-- JavaScript to Automatically Submit Image Form on File Selection -->
                        <script>
                            function submitImageForm() {
                                document.getElementById('imageForm').submit();
                            }
                        </script>


                    </div>

                    {{--the form for global Groups  --}}

                    <div class="Global_Groups_container">
                        <!-- Add this form above the groups list in your Blade template -->
                        <div class="search-bar">
                            <input type="text" id="searchinput" class="form-control" placeholder="Search groups...">
                        </div>

                        <!-- Existing Groups List -->
                        <ul id="Groupsalllist" class="list-group">
                            @forelse($groups as $group)
                                <li class="list-group-item group-item" data-group-id="{{ $group->id }}">
                                    <img src="/storage/group_pics/{{ $group->group_pic }}" alt="{{ $group->name }} image">
                                    <strong class="mr-2">{{ $group->name }}</strong>
                                    Admin: {{ $group->creator->name }}
                                </li>
                            @empty
                                <li class="list-group-item">There are no groups</li>
                            @endforelse
                        </ul>
                    </div>

                </div>




                <!-- Button to Trigger Modal -->
                <button id="open-modal-btn" class="CNG_btn">Create New Group</button>

                <!-- Modal Structure -->
                <div id="group-modal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>

                        <div class="Group_Creation_container">
                            <h2>Create a New Group</h2>
                            <form action="{{ route('groups.store') }}" method="POST">
                                @csrf
                                <div class="form-group mb-4">
                                    <label for="group-name" class="form-label">Group Name</label>
                                    <input type="text" class="form-control" id="group-name" name="name" required>
                                </div>

                                <div class="form-group mb-4">
                                    <label for="group-users" class="form-label">Add Users</label>
                                    <div id="group-users">
                                        @foreach($users as $user)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="user-{{ $user->id }}" name="users[]" value="{{ $user->id }}">
                                                <label class="form-check-label" for="user-{{ $user->id }}">
                                                    {{ $user->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Create Group</button>
                            </form>
                        </div>
                    </div>
                </div>


                <!-- Logout Button -->
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout_btn">Logout</button>
                </form>


                <div class="account-settings">
                    <!-- Spaceship Toggle Switch -->
                    <span class="toggle-label">Delete Account</span>
                    <label class="switch">
                        <input type="checkbox" id="toggleSwitch" onclick="toggleDeleteAccountForm()">
                        <span class="slider"></span>
                    </label>

                    <!-- Delete Account Form (Initially Hidden) -->
                    <div id="deleteAccountForm" style="display: none; margin-top: 20px;">
                        <form action="{{ route('account.delete') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Account</button>
                        </form>
                    </div>
                </div>

            </div>

        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- Script to Handle sliders in mobile view -->
        <script>
            document.querySelector('.open-chat-box').addEventListener('click', function() {
                document.querySelector('.Chat_box_container').classList.add('open');
            });

            document.querySelector('.close-chat-box').addEventListener('click', function() {
                document.querySelector('.Chat_box_container').classList.remove('open');
            });

            // Get modal element and button
            const modal = document.getElementById('group-modal');
            const openModalBtn = document.getElementById('open-modal-btn');
            const closeModalBtn = document.querySelector('.modal-content .close');

            // Open modal when button is clicked
            openModalBtn.addEventListener('click', function() {
                modal.style.display = 'flex';
            });

            // Close modal when close button is clicked
            closeModalBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            // Close modal when clicking outside the modal content
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        </script>

        <!-- Include a script to handle live search groups -->
        <script>
            $(document).ready(function() {
                $('#searchinput').on('keyup', function() {
                    var query = $(this).val();
                    $.ajax({
                        url: "{{ route('groups.search') }}",
                        type: "GET",
                        data: { query: query },
                        success: function(data) {
                            var html = '';
                            if (data.groups.length === 0) {
                                html = '<li class="list-group-item">There are no groups</li>';
                            } else {
                                $.each(data.groups, function(index, group) {
                                    html += '<li class="list-group-item group-item" data-group-id="' + group.id + '">';
                                    html += '<img src="/storage/group_pics/' + group.group_pic + '">';
                                    html += '<strong class="mr-2">' + group.name + '</strong>';
                                    html += 'Admin: ' + group.creator.name;
                                    html += '</li>';
                                });
                            }
                            $('#Groupsalllist').html(html);
                        }
                    });
                });
            });
        </script>

        <!-- Script to Handle Toggle -->
        <script>
            function toggleDeleteAccountForm() {
                var deleteForm = document.getElementById('deleteAccountForm');
                var toggleSwitch = document.getElementById('toggleSwitch');

                // Toggle visibility of the form based on the switch
                if (toggleSwitch.checked) {
                    deleteForm.style.display = 'block';  // Show the form
                } else {
                    deleteForm.style.display = 'none';   // Hide the form
                }
            }
        </script>

    </body>
</html>
