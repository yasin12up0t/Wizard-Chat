{{--start chatlist section--}}
<div style="height: 100%">
    <div style="max-height: 50%">
        <input type="text" id="search" class="form-control" placeholder="Search users...">
        <ul id="user-list" class="list-group">
            <!-- User list will be dynamically populated here -->
        </ul>

        <h5><i class="fa-solid fa-user"></i> Conversations</h5>
        <ul id="conversation-list" class="list-group">
            <!-- Conversation list will be dynamically populated here -->
        </ul>
    </div>

    <div style="max-height: 50%">
        @php
            use App\Models\Group;

            $groups = Group::whereHas('users', function($query) {
                $query->where('user_id', Auth::id());
            })->get();
        @endphp
        <h5><i class="fa-solid fa-users"></i> Groups</h5>
        <ul id="Groups-list" class="list-group">
            @foreach($groups as $group)
                <li class="list-group-item group-item" data-group-id="{{ $group->id }}">
                    <img src="/storage/group_pics/{{ $group->group_pic }}">
                    {{ $group->name }}
                </li>
            @endforeach
        </ul>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        const currentUserId = {{ Auth::id() }};
        const currentUrl = window.location.href;
        const urlParams = new URLSearchParams(window.location.search);
        const chatUserId = urlParams.get('userId') || (currentUrl.includes('/chat/') ? currentUrl.split('/').pop() : null);
        const groupIdFromUrl = urlParams.get('groupId') || (currentUrl.includes('/groups/') ? currentUrl.split('/').pop() : null);

        // Function to render user list
        const renderUserList = (data) => {
            const $userList = $('#user-list');
            $userList.empty();
            if (data.length) {
                data.forEach(user => {
                    $userList.append(`
                        <li class="list-group-item user-item" data-user-id="${user.id}">
                            <img src="/storage/profile_pics/${user.profile_pic}">
                            ${user.name}
                        </li>
                    `);
                });
            } else {
                $userList.append('<li class="list-group-item">No users found</li>');
            }
        };

        // Function to fetch and update conversation users
        const fetchConversationUsers = () => {
            $.get('/conversation-users', data => {
                const $conversationList = $('#conversation-list');
                $conversationList.empty();
                if (data.length) {
                    data.forEach(user => {
                        const isActive = user.id == chatUserId ? 'background-color: #FFA500;' : '';

                        // Truncate the last message to the first 10 characters
                        const lastMsgPreview = user.last_message ? user.last_message.substring(0, 10) + '...' : 'No messages yet';
                        const lastMsgDate = user.last_message_date ? user.last_message_date : '';
                        const unseenCount = user.unseen_count > 0 ? `<div class="msgscount">${user.unseen_count}</div>` : '';

                        $conversationList.append(`
                            <li class="list-group-item conversation-item" style="${isActive}" data-user-id="${user.id}">
                                <img src="/storage/profile_pics/${user.profile_pic}">
                                ${user.name}
                                <div class="lastmsg">${lastMsgPreview}</div>
                                <div class="msgsdate">${lastMsgDate}</div>
                                ${unseenCount}
                            </li>
                        `);

                    });
                } else {
                    $conversationList.append('<li class="list-group-item">No conversations</li>');
                }
            });
        };

        // Function to highlight the selected group
        const highlightSelectedGroup = () => {
            if (groupIdFromUrl) {
                $('.group-item').each(function() {
                    const groupId = $(this).data('group-id');
                    if (groupId == groupIdFromUrl) {
                        $(this).css('background-color', '#FFA500');
                    } else {
                        $(this).css('background-color', '');
                    }
                });
            }
        };

        // Handle live search yellow
        $('#search').on('keyup', function() {
            const query = $(this).val().toLowerCase();
            if (query) {
                $.get('/search-users', { query }, renderUserList);
            } else {
                $('#user-list').empty();
            }
        });

        // Handle user and conversation selection
        $(document).on('click', '.user-item, .conversation-item', function() {
            const userId = $(this).data('user-id');
            window.location.href = `/chat/${userId}`;
        });

        // Handle group selection
        $(document).on('click', '.group-item', function() {
            const groupId = $(this).data('group-id');
            window.location.href = `/groups/${groupId}`; // Pass the groupId in URL
        });

        // Fetch conversation users on page load and periodically
        fetchConversationUsers();
        setInterval(fetchConversationUsers, 2000); // Update every 2 seconds

        // Highlight the selected item based on URL
        if (currentUrl.includes('/groups/')) {
            highlightSelectedGroup();
        } else if (currentUrl.includes('/chat/')) {
            fetchConversationUsers(); // Ensure conversations are fetched and updated
        }
    });
</script>
