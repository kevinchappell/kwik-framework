jQuery(document).ready(function($) {

  // yes I am aware that binding events on document is lame
  // but sort of needed for this usage.
  $(document).on('click', '.upload_img, .img_prev', function() {
    var button = $(this);
    var _orig_send_attachment = wp.media.editor.send.attachment,
        _custom_media = true;

    wp.media.editor.send.attachment = function(props, attachment) {
      if (_custom_media) {
        var img_id = button.siblings('.img_id');
        button.siblings('.img_title').html(attachment.title + '<span class="clear_img tooltip" title="Remove Image"></span>');
        img_id.val(attachment.id);
        img_id.trigger('change');
        if (button.hasClass('img_prev')) {
          button.attr('src', attachment.url);
        } else {
          button.siblings('.img_prev').attr('src', attachment.url);
        }
      } else {
        return _orig_send_attachment.apply(this, [props, attachment]);
      }
    };

    wp.media.editor.open(button);
    return false;
  });

  $('.add_media').on('click', function() {
    _custom_media = false;
  });

  $('.clear_img').live("click", function() {
    var img_ttl = $(this).parent('.img_title');
    img_ttl.empty();
    img_ttl.siblings('.img_id').val('');
    img_ttl.siblings('.img_prev').attr('src', '');
  });

  $('.kf_settings').tabs({
    beforeActivate: function(event, ui) {
      window.location.hash = ui.newPanel.selector;
    }
  });

  $( "input, select, textarea", '.kf_option.error' ).keydown(function() {
    var option_row = $(this).parents('tr');
    if(!option_row.hasClass('error')) {
      return;
    }
    option_row.removeClass('error');
    $('.error_icon', option_row).fadeOut(500);
  });

});
