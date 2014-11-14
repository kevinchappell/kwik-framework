jQuery(document).ready(function ($) {

  var _custom_media = true,
    _orig_send_attachment = wp.media.editor.send.attachment;

  // yes I am aware that binding events on document is lame.
  $(document).on('click', '.upload_img, .img_prev', function () {
    console.log($(this));
    var button = $(this),
      _custom_media = true;

    wp.media.editor.send.attachment = function (props, attachment) {
      if (_custom_media) {
        var img_id = button.siblings('.img_id');
        button.siblings('.img_title').html(attachment.title + '<span class="clear_img tooltip" title="Remove Image"></span>');
        img_id.val(attachment.id);
        img_id.trigger('change');
        button.siblings('.img_prev').attr('src', attachment.url);
      } else {
        return _orig_send_attachment.apply(this, [props, attachment]);
      }
    };

    wp.media.editor.open(button);
    return false;
  });

  $('.add_media').on('click', function () {
    _custom_media = false;
  });

  $('.clear_img').live("click", function () {
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



});
