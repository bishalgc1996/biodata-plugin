console.log('Test');


(function($) {
  // Your code here
  // Use $ inside this function scope
  // For example:
 

  $('.approve-button').on('click', function() {
    var userId = $(this).data('user-id');
    
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'approve_user',
            user_id: userId
        },
        success: function(response) {
            // Handle success response
            console.log(response);
        },
        error: function(error) {
            // Handle error response
            console.log(error);
        }
    });
});


const approveButtons = document.querySelectorAll('.approve-button');
  approveButtons.forEach(button => {
    const status = button.parentNode.previousElementSibling.textContent;
    if (status === 'pending') {
      button.textContent = 'Approve';
    } else if (status === 'approved') {
      button.textContent = 'Approved';
      button.classList.add('gray-button');
    }
  });





})(jQuery);

