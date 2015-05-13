jQuery(document).ready(function($) {
	var utils = {};

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


	utils.getAjaxSource = function(autocomplete, request, response) {
		var element = autocomplete.element,
				searchTerm = request.term;

		var formData = {
					action: 'kf_query_cpt',
					post_type: element.data('link-to'),
					term: searchTerm
				};

		// check if user has a custom action
		if (element.data('ajax-action')) {
			formData.action = element.data('ajax-action');
		}

		$.post(window.ajaxurl, formData, function(data) {
			 response(data);
		}, 'json');
	};

		// create: function() {
		// 	$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
		// 		ul.addClass('magazine-search').toggleClass('no-results', !item.remoteID);
		// 		return $('<li>')
		// 			.data('ui-autocomplete-item', item)
		// 			.append($('<a>').append(utils.searchResult(item)))
		// 			.appendTo(ul);
		// 	};
		// },
	function kfAutocomplete() {
		$('.kf_autocomplete').autocomplete({
			delay: 333,
			source: function (request, response) {
				return utils.getAjaxSource(this, request, response);
			},
			change: function( event, ui ) {
				var $cptInputID = $(this).parent().siblings('input.cpt_id'),
						$cptInputLabel = $(this),
						$cptImage = $(this).siblings('.kf_prev_img');
						if ($cptInputLabel.val() === '') {
							$cptInputID.val('');
							$cptInputLabel.val('');
							$cptImage.remove();
						}
			},
			select: function( event, ui ) {
				var $cptInputID = $(this).parent().siblings('input.cpt_id'),
						$cptInputLabel = $(this),
						$cptImage = $(this).siblings('.kf_prev_img'),
						img = ui.item.image;
						$cptInputID.val(ui.item.id);
				if (img){
					$cptImage.remove();
					$cptInputLabel.before(img);
				}
			},
			minLength: 3
		});
	}

	kfAutocomplete();

});
