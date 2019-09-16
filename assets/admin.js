(function($, window, document, undefined) {
  $(function() {
    var image = $(".wp-cache-clear-webhook-badge img");
    var imageSrc = image.prop("src");
    var refreshTimout = null;

    var updateNetlifyBadgeUrl = function() {
      if (!image.length) {
        return;
      }
      var d = new Date();
      image.prop("src", imageSrc + "?v=s_" + d.getTime());
      refreshTimout = setTimeout(updateNetlifyBadgeUrl, 15000);
    };

    refreshTimout = setTimeout(updateNetlifyBadgeUrl, 15000);

    $(".wp-cache-clear-webhook-button").click(function(e) {
      e.preventDefault();
      $.ajax({
        type: "POST",
        url: wpjd.ajaxurl,
        data: {
          action: "wp_cache_clear_webhook_manual_trigger",
          security: wpjd.deployment_button_nonce
        },
        dataType: "json",
        success: updateNetlifyBadgeUrl
      });
      clearTimeout(refreshTimout);
    });
  });
})(jQuery, window, document);
