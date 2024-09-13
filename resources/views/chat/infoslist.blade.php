@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="stylesheet" href="{{ asset('css/chats_infolist.css') }}">

<div class="profile-container">
    <!-- Profile Cover -->
    <img id="cover-photo" src="default-cover.jpg" alt="Cover Photo" class="cover-photo">

    <!-- Profile Picture -->
    <img id="profile-photo" src="default-profile.jpg" alt="Profile Photo" class="profile-photo">

    <!-- User Info -->
    <div id="user-info" class="user-info card-body">
        <h5 class="card-title" id="user-name">Loading...</h5>
        <p class="card-text" id="user-email">Loading...</p>
        <p class="card-text" id="user-bio">Loading...</p>
        <p class="card-text" id="user-gender">Loading...</p>
        <p class="card-text" id="user-phone">Loading...</p>
    </div>
</div>

<!-- Ensure $conversationId is defined in the view -->
@if(isset($conversationId))
    <form action="{{ route('chat.conversation.delete', $conversationId) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this conversation?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="Delete_Conversation_btn">Delete Conversation</button>
    </form>
@else
    <p>No conversation selected to delete.</p>
@endif

<script>
    $(document).ready(function() {
        // Function to extract the user ID from the URL
        function getUserIdFromUrl() {
            const pathArray = window.location.pathname.split('/');
            return pathArray[pathArray.length - 1]; // Get the last part of the URL
        }

        // Function to load user info using the extracted user ID
        function loadUserInfo(userId) {
            $.get(`/user/details/${userId}`, function(data) {
                // Update the user's name, email, bio, gender, and phone
                $('#user-name').text(data.name);
                $('#user-email').text(data.email);
                $('#user-bio').text(data.bio ? data.bio : 'No bio available');
                $('#user-gender').text(data.gender ? data.gender : 'Gender not specified');
                $('#user-phone').text(data.phone ? data.phone : 'Phone number not provided');

                // Update the profile and cover photos
                $('#profile-photo').attr('src', data.profile_pic ? `/storage/profile_pics/${data.profile_pic}` : 'default-profile.jpg');
                $('#cover-photo').attr('src', data.profile_cover ? `/storage/profile_cover/${data.profile_cover}` : 'default-cover.jpg');
            }).fail(function() {
                $('#user-name').text('User not found');
                $('#user-email').text('Unable to fetch user details');
                $('#user-bio').text('Unable to fetch user details');
                $('#user-gender').text('Unable to fetch user details');
                $('#user-phone').text('Unable to fetch user details');
            });
        }

        // Extract user ID from the URL and load user info
        const userId = getUserIdFromUrl();
        if (userId) {
            loadUserInfo(userId);
        } else {
            $('#user-name').text('No user selected');
            $('#user-email').text('Please select a user to view details');
            $('#user-bio').text('Please select a user to view details');
            $('#user-gender').text('Please select a user to view details');
            $('#user-phone').text('Please select a user to view details');
        }
    });
</script>
