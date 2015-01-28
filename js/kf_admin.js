jQuery(document).ready(function($) {

  // yes I am aware that binding events on document is lame
  // but sort of needed for this usage.
  $(document).on('click', '.upload_img, .img_prev', function() {
    var button = $(this),
      field = button.parent('.kf_img_wrap'),
      originSendAttachment = wp.media.editor.send.attachment,
      customMedia = true;

    $('.add_media').on('click', function() {
      customMedia = false;
    });

    wp.media.editor.send.attachment = function(props, attachment) {
      if (customMedia) {
        var imgID = $('.img-id', field);
        $('.img_title', field).html(attachment.title + '<span class="clear_img tooltip" title="Remove Image"></span>');
        imgID.val(attachment.id).trigger('change');
        $('.img_prev', field).css('background-image', 'url(' + attachment.url + ')').removeClass('no-image');
      } else {
        return originSendAttachment.apply(this, [props, attachment]);
      }
    };

    wp.media.editor.open(button);
    return false;
  });

  $('.clear_img').on('click', function() {
    var field = $(this).parents('.kf_field:eq(0)');
    $('.img_title', field).empty();
    $('.img-id', field).val('');
    $('.img_prev', field).css('background-image', 'none').addClass('no-image');
  });

  $('.kf_settings').tabs({
    beforeActivate: function(event, ui) {
      window.location.hash = ui.newPanel.selector;
    }
  });

  $('input, select, textarea', '.kf_option.error').keydown(function() {
    var optionRow = $(this).parents('tr');
    if (!optionRow.hasClass('error')) {
      return;
    }
    optionRow.removeClass('error');
    $('.error_icon', optionRow).fadeOut(500);
  });

});
