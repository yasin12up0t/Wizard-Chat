<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>chats</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/show_chats.css') }}">
    </head>
    <body>
        <div class="Main_container">
            <div class="chatlist_container">
                @include('chat.chatslist') <!-- Include the chat list template -->
            </div>

            <div class="Chat_box_container">
                <div class="Chat_box_header">
                    <button id="chat-list-btn">Chat List <i class="fa-solid fa-comments"></i></button>
                    <button id="info-btn">Info <i class="fa-solid fa-info-circle"></i></button>
                    <button onclick="window.location='{{ route('chat.index') }}'">Home <i class="fa-solid fa-home"></i></button>
                </div>
                <div id="chat-box">
                    <!-- Messages will be dynamically loaded here -->
                </div>

                <div class="input-group">
                    <textarea id="message" class="form-control" placeholder="Type a message..."></textarea>
                    <input type="file" id="file-input" class="form-control" style="display:none;" />
                    <button id="files" class="btn">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                    <button id="send">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                    <button id="record" class="btn">
                        <i id="record-icon" class="fa-solid fa-microphone"></i>
                    </button>
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
                </div>
            </div>

            <!-- Info List Container -->
            <div class="InfoList_container" id="infoList">
                <button id="close_infolist"><i class="fa-solid fa-arrow-left"></i> close Info's List</button>

                @include('chat.infoslist') <!-- Include the info list template -->
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                    formData.append('audio', blob, 'recording.wav');
                    formData.append('to_user_id', '{{ $user->id }}'); // Pass the receiver's user ID
                    formData.append('_token', '{{ csrf_token() }}'); // Add CSRF token

                    $.ajax({
                        url: '{{ route('chat.uploadRecording') }}', // Use named route here
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
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('to_user_id', '{{ $user->id }}'); // Pass the receiver's user ID
                    formData.append('_token', '{{ csrf_token() }}'); // Add CSRF token

                    $.ajax({
                        url: '{{ route('chat.uploadFile') }}', // Use named route here
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
                const userId = '{{ $user->id }}'; // The ID of the user you're chatting with
                const chatBox = $('#chat-box'); // The chat box container
                const messageInput = $('#message'); // The message input textarea
                let lastMessageId = null;
                let replyToMessageId = null;

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
                    $.get(`/chat/messages/${userId}`, function (data) {

                        // Create a map of messages by their ID for quick lookup
                        const messageMap = new Map();
                        data.forEach(message => {
                            messageMap.set(message.id, message);
                        });

                        // Extract the IgroupDs of the fetched messages
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

                        // Check the seen status of all messages
                        updateSeenStatusIcons(data);
                        // Re-initiate long-polling
                        pollForNewMessages();

                    });
                }

                // Function to append new messages to the chat box
                function appendMessages(messages) {
                    messages.forEach(function (message) {
                        const isSent = message.from_user_id === {{ Auth::id() }};
                        const messageClass = isSent ? 'sent' : 'received';

                        let content = '';

                        // Check if the message is a reply to another message
                        if (message.reply_to_message_id) {
                            const repliedMessage = messages.find(m => m.id === message.reply_to_message_id);
                            if (repliedMessage) {
                                const repliedContent = escapeHtml(repliedMessage.message).replace(/\n/g, '<br>');
                                content += `<div class="replied-message"><strong>Replying to:</strong> ${repliedContent}</div>`;
                            }
                        }

                        if (message.message !== null) {
                            content += escapeHtml(message.message).replace(/\n/g, '<br>');
                        }

                        if (message.audio_path !== null) {
                            content += `<audio controls src="/storage/audio_msgs/${message.audio_path}"></audio>`;
                        }

                        if (message.attachments !== null) {
                            const fileUrl = `/storage/${message.attachments}`;
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

                        const timestamp = message.created_at;
                        const seenClass = message.seen ? 'fa-check-double' : 'fa-check';
                        const statusIcon = isSent ? `<i class="fa-solid ${seenClass} status-icon"></i>` : '';

                        const deleteButton = isSent ? `<button class="delete_msg_btn" data-message-id="${message.id}"><i class="fa-solid fa-trash"></i></button>` : '';
                        const replyButton = `<button class="reply_msg_btn" data-message-id="${message.id}"><i class="fa-solid fa-reply"></i> Reply </button>`;

                        chatBox.append(`
                            <div class="message ${messageClass}" data-message-id="${message.id}">
                                <div class="message-content">
                                    ${content}
                                </div>
                                <div class="message-footer">
                                    ${deleteButton}
                                    ${replyButton}
                                    <div class="message-time">${timestamp}</div>
                                    <div class="status-container">${statusIcon}</div>
                                </div>
                            </div>
                        `);
                    });

                    chatBox.scrollTop(chatBox[0].scrollHeight); // Scroll to the bottom
                }

                // Function to update the seen status icons
                function updateSeenStatusIcons(messages) {
                    messages.forEach(function (message) {
                        const messageElement = $(`.message[data-message-id="${message.id}"]`);
                        const statusIconElement = messageElement.find('.status-icon');

                        if (message.seen) {
                            statusIconElement.removeClass('fa-check').addClass('fa-check-double');
                        } else {
                            statusIconElement.removeClass('fa-check-double').addClass('fa-check');
                        }
                    });
                }

                // Mark messages as seen when they become visible
                function markMessagesAsSeen() {
                    $('.message').each(function() {
                        const messageId = $(this).data('message-id');
                        const statusIconElement = $(this).find('.status-container i');

                        if (statusIconElement.hasClass('fa-check')) {
                            console.log(`Marking message ${messageId} as seen`);
                            $.post(`/chat/messages/${messageId}/seen`, {
                                _token: '{{ csrf_token() }}'
                            }, function() {
                                console.log(`Message ${messageId} marked as seen`);
                                statusIconElement.removeClass('fa-check').addClass('fa-check-double');
                            }).fail(function(xhr) {
                                console.error(`Error marking message ${messageId} as seen: ${xhr.responseText}`);
                            });
                        }
                    });
                }

                // Handle clicking the reply button
                chatBox.on('click', '.reply_msg_btn', function () {
                    replyToMessageId = $(this).data('message-id');
                    const originalMessageContent = $(`.message[data-message-id="${replyToMessageId}"] .message-content`).html();
                    messageInput.focus();
                    messageInput.val(`Replying to: ${originalMessageContent}\n\n`); // Pre-fill the input with the content being replied to
                });

                // Function to send a message with a reply reference
                function sendMessage() {
                    const message = messageInput.val();
                    if (message) {
                        $.post('{{ route("chat.send") }}', {
                            to_user_id: userId,
                            message: message,
                            reply_to_message_id: replyToMessageId, // Include the reply_to_message_id
                            _token: '{{ csrf_token() }}'
                        }, function (data) {
                            messageInput.val(''); // Clear the input field
                            replyToMessageId = null; // Clear the reply message id
                            fetchMessages(); // Refresh messages after sending
                        });
                    }
                }

                // Function to delete a message
                function deleteMessage(messageId) {
                    $.ajax({
                        url: `/chat/messages/${messageId}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function () {
                            fetchMessages(); // Refresh messages after deletion
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

                // Fetch initial messages
                fetchMessages();

                // Poll for new messages every 5 seconds
                setInterval(function() {
                    fetchMessages();
                    markMessagesAsSeen(); // Mark messages as seen periodically
                }, 5000);
            });
        </script>
    </body>
</html>
