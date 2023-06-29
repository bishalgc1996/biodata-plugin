(function ($) {
  // Your code here
  // Use $ inside this function scope
  // For example:

  $(document).ready(function () {
    // Handle the change event of the occupation filter
    $("#occupation-filter").on("change", function () {
      var selectedOccupation = $(this).val();

      // Make the AJAX request to filter the biodata cards
      $.ajax({
        url: ajax_object.ajaxurl, // The AJAX URL (make sure to localize this variable)
        type: "POST",
        data: {
          action: "filter_biodata_cards", // The AJAX action hook
          occupation: selectedOccupation, // Selected occupation value
        },
        beforeSend: function () {
          // Display a loading spinner or any visual indicator
          // to indicate that the filtering is in progress
          $("#biodata-cards-container").html(
            '<div class="loading-spinner"></div>'
          );
        },
        success: function (response) {
          // Update the biodata cards container with the filtered HTML
          $(".biodata-wrapper").html(response);
          console.log(response);
        },
        error: function (xhr, status, error) {
          // Handle any error that occurred during the AJAX request
          console.log("AJAX Error: " + error);
        },
      });
    });

    if (document.getElementById("password") !== null) {
      // Add event listener for form submission
      document
        .getElementById("password")
        .addEventListener("input", function (event) {
          // Get the password input field
          var passwordField = document.getElementById("password");
          console.log("value");
          var passwordValue = passwordField.value;

          // Display error message
          var passwordError = document.getElementById("password-error");

          // Check if password is less than 8 characters
          if (passwordValue.length < 8) {
            // Disable form submission
            document
              .getElementById("biodata-registration-form")
              .addEventListener("submit", function (event) {
                event.preventDefault();
              });
            passwordError.style.display = "block";
            if (passwordError !== null) {
              passwordError.textContent =
                "Password must be at least 8 characters long.";
            }
            passwordField.classList.add("error");
          } else {
            passwordError.style.display = "none";
          }
        });
    }
  });
})(jQuery);
