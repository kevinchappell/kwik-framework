jQuery(document).ready(function ($) {



  var _custom_media = true,
    _orig_send_attachment = wp.media.editor.send.attachment;

  $('.upload_img, .img_prev').on('click', function () {
    var button = $(this),
      _custom_media = true;

    wp.media.editor.send.attachment = function (props, attachment) {
      if (_custom_media) {
        //$("#"+id).val(attachment.id);
        button.siblings('.img_title').html(attachment.title + '<span class="clear_img tooltip" title="Remove Image"></span>');
        button.siblings('.img_id').val(attachment.id);
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



});
