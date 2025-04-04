$(document).ready(function() {
  // Get Report passenger
  $(document).on('click', '#user_log', function(e) {
      e.preventDefault(); // Prevent default form submission behavior
      
      // Get filter values
      var date_sel_start = $('#date_sel_start').val();
      var date_sel_end = $('#date_sel_end').val();
      var time_sel = $("input[name='time_sel']:checked").val(); // Fixed selector
      var time_sel_start = $('#time_sel_start').val();
      var time_sel_end = $('#time_sel_end').val();
      var fing_sel = $('#fing_sel').val(); // Simplified selector
      var dev_id = $('#dev_sel').val(); // Simplified selector
      
      // Validate date range
      if (date_sel_start && date_sel_end && new Date(date_sel_start) > new Date(date_sel_end)) {
          bootbox.alert("End date must be after start date");
          return false;
      }

      // Show loading indicator
      $('.up_info2').text("Applying filters...").fadeIn(500);
      
      // Single AJAX call to get filtered data
      $.ajax({
          url: 'user_log_up.php',
          type: 'POST',
          data: {
              'filter_logs': 1, // Changed from 'log_date' to be consistent with PHP
              'start_date': date_sel_start, // Renamed for consistency
              'end_date': date_sel_end, // Renamed for consistency
              'time_type': time_sel, // Renamed for consistency
              'start_time': time_sel_start, // Renamed for consistency
              'end_time': time_sel_end, // Renamed for consistency
              'finger_id': fing_sel, // Renamed for consistency
              'device_id': dev_id // Renamed for consistency
          },
          success: function(data) {
              // Update UI
              $('#userslog').html(data);
              $('.up_info2').text("Filters applied successfully!");
              
              // Hide modal
              $('#Filter-export').modal('hide');
              
              // Hide success message after delay
              setTimeout(function() {
                  $('.up_info2').fadeOut(500);
              }, 3000);
          },
          error: function(xhr, status, error) {
              console.error("AJAX Error:", status, error);
              $('.up_info2').text("Error applying filters").css('color', 'red');
              setTimeout(function() {
                  $('.up_info2').fadeOut(500);
              }, 3000);
          }
      });
  });
});