require(["jquery", "mage/cookies"], function ($) {
  $(document).ready(function () {
    var idleTime = 0;

    function resetTimer() {
      idleTime = 0;
    }

    // Increment the idle time counter every second
    var idleInterval = setInterval(timerIncrement, 1000); // 1 second

    function timerIncrement() {
      idleTime++;
      if (idleTime >= 10 && !$.mage.cookies.get("popup_shown")) {
        // Show the popup after 10 seconds of inactivity
        $("#popup-overlay").show();

        // Create a date 30 days in the future
        var expiryDate = new Date();
        expiryDate.setDate(expiryDate.getDate() + 30);

        // Set the cookie to indicate that the popup has been shown with a 30-day expiry
        $.mage.cookies.set("popup_shown", "true", {
          expires: expiryDate,
        });
      }
    }

    // Reset the timer on mouse movement or keypress
    $(this).mousemove(resetTimer);
    $(this).keypress(resetTimer);
    $(this).on("touchstart", resetTimer);
    $(this).on("scroll", resetTimer);

    // Close the popup when the close button is clicked
    $("#popup-close").on("click", function () {
      $("#popup-overlay").hide();
    });
  });
});
