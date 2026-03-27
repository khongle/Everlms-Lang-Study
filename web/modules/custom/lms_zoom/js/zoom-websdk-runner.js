(function ($, Drupal, once) {
  Drupal.behaviors.zoomWebSdk = {
    attach: function (context, settings) {
      $(once('zoomWebSdkRunner', '#lms-zoom-meeting-container', context)).each(function () {
        var $container = $(this);
        var meetingId = $container.data('meeting-id');
        var passcode = $container.data('passcode') || '';
        var userName = $container.data('user-name') || 'Student';
        var userEmail = $container.data('user-email') || '';
        
        // Hide scrollbars for full screen experience
        $('html, body').css('overflow', 'hidden');

        // Zoom API requires meetingNumber as a string without spaces
        meetingId = String(meetingId).replace(/\s/g, '');

        // 1. Zoom SDK Pre-Configuration using CDN
        try {
          // Important: You must use the same version as the CDN scripts
          ZoomMtg.setZoomJSLib('https://source.zoom.us/3.1.6/lib', '/av');
          ZoomMtg.preLoadWasm();
          ZoomMtg.prepareWebSDK();
        } catch (e) {
          showError('Lỗi tải thư viện Zoom: ' + e.message);
          return;
        }

        // 2. Fetch Signature from Backend API
        $.ajax({
          url: '/api/lms/websdk/signature',
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({
            meetingNumber: meetingId,
            role: 0 // 0 means attendee
          }),
          success: function(response) {
            if (response.signature && response.sdkKey) {
               startMeeting(response.signature, response.sdkKey, response.zak);
            } else {
               showError('Máy chủ gửi về Signature trống. Không thể vào lớp.');
            }
          },
          error: function(xhr) {
             console.error("Signature XHR Error:", xhr);
             var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Máy chủ từ chối kết nối.';
             showError('Lỗi xác thực: ' + msg);
          }
        });

        // 3. Init Zoom Web SDK and Join Meeting
        function startMeeting(signature, sdkKey, zakToken) {
          ZoomMtg.init({
            leaveUrl: '/my-courses', // Where to redirect on leave
            screenShare: true,       // Force enable the Share Screen button
            isSupportAV: true,       // Force enable Audio/Video
            isSupportChat: true,
            isSupportQA: true,
            isSupportCC: true,
            disableRecord: false,
            disableJoinAudio: false,
            success: function(success) {
              console.log('ZoomMtg.init success');
              
              $('#lms-zoom-meeting-container').hide(); // Hide the overlay completely so Zoom UI can show

              var joinParams = {
                sdkKey: sdkKey,
                signature: signature,
                meetingNumber: meetingId,
                userName: userName,
                userEmail: userEmail,
                passWord: passcode,
                success: function(res) {
                  console.log('join meeting success');
                },
                error: function(res) {
                  console.error("Error joining meeting:", res);
                  var errText = res.errorMessage || res.result || 'Unknown error';
                  if (errText.indexOf('The Meeting Number is not found') !== -1) {
                      errText = 'Phòng Zoom chưa được mở hoặc ID phòng (' + meetingId + ') không tồn tại.';
                  }
                  
                  // Show the overlay again to display the error
                  $('#lms-zoom-meeting-container').show();
                  showError('Lỗi vào phòng học: ' + errText);
                }
              };

              // If ZAK token is provided (Host), append it to bypass the waiting room
              if (zakToken) {
                joinParams.zak = zakToken;
              }

              ZoomMtg.join(joinParams);
            },
            error: function(res) {
              console.log('Zoom init error:', res);
              showError('Lỗi khởi tạo Zoom Web SDK.');
            }
          });
        }

        // Error rendering function
        function showError(msg) {
          $('#zmmtg-loading-indicator').html('<div style="background: rgba(255,123,114,0.1); border: 1px solid rgba(255,123,114,0.3); color: #ff7b72; padding: 1.5rem 2rem; border-radius: 8px;"><h3><i class="fas fa-exclamation-triangle"></i> Lỗi</h3><p style="margin-top: 1rem;">' + msg + '</p><a href="/my-courses" style="color: white; margin-top: 1rem; display: inline-block; text-decoration: underline;">Quay lại danh sách khóa học</a></div>');
        }

      });
    }
  };
})(jQuery, Drupal, once);
