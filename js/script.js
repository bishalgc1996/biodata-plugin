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
  });
})(jQuery);
