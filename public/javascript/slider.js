


var jEBE_slider_cache_option = {},
	jEBE_slider_dang_scroll = false,
	jEBE_active_swipe_slider = {};

/*
* jd: ID hoặc class của thẻ HTML cần tạo slider. Ví dụ: .slider1, #slider2
*
*
* conf: cấu hình riêng cho slider, các cấu hình mặc định sẽ được thiết lập nếu không có cấu hình cụ thể
* Danh sách các cấu hình:
*
// tự động chạy (true|false) nếu được set là true (mặc định false)
autoplay: true
// cho phép chuyển ảnh trên mobile bẳng touch (true|false) nếu được set là true (mặc định false)
swipemobile: true
// hiển thị nút bấm chuyển ảnh (true|false) nếu được set là true (mặc định true)
buttonListNext: true
// kích thước cố định (mặc định là tự động tính theo tham số size bên dưới)
width: 1
height: 1
// tỷ lệ giữa chiều cao và chiều rộng, thiết lập dưới dạng: height/width (thay height và width bằng các con số cụ thể)
size: "width/height"
// thiết lập chiều cao dòng cho phần slider (ít khi sử dụng vì nó sẽ được tự động tính toán)
lineHeight: ""
// ẩn (true|false) nếu không có LI nào được tìm thấy trong slider (mặc định false)
hide_if_null: true
// mặc định là điều khiển các thẻ LI làm slider, nếu muốn thẻ khác hãy đặt theo class hoặc ID. Ví dụ: .node1, #node2
for_class: li
// Nút bấm chuyển ảnh trên slider (true|false) (mặc định false)
sliderArrow: true
// Icon cho nút bấm, sử dụng Font Awesome: http://fontawesome.io/icons/
sliderArrowLeft: "fa-angle-left"
sliderArrowRight: "fa-angle-right"
// Kích thước cho nút bấm (font-size), tính theo pixel (px)
sliderArrowSize: 30
// Chiều rộng cố định cho nút bấm (mặc định là tự động thiết lập)
sliderArrowWidthLeft: "auto"
sliderArrowWidthRight: "auto"
// tốc độ chuyển slider
speed: 0
// giãn cách chuyển slider nếu tính năng tự động chạy được bật
speedNext: 5000
thumbnail: false
thumbnailWidth: 90
thumbnailHeight: 90
// Tạo slider cho thumbnail
thumbnailSlider: true
// Số lượng thẻ LI muốn hiển thị trên mỗi loạt slider
visible: 1
// hiển thị ngẫu nhiên 1 ảnh nào đó
showRandom: false
*
*
*
* callBack: function sẽ được chạy sau khi tạo slider xong, thường dùng để xử lý các chức năng riêng ngoài việc tạo slider mặc định
*/
function jEBE_timeout_slider ( jd, conf, timeo, callBack ) {
	
	if ( typeof conf != 'object' ) {
		conf = {};
	}
	
	//
	if ( typeof callBack != 'function' ) {
		callBack = null;
	}
	
	// hẹn giờ mặc định để tạo slider
	if ( typeof timeo != 'number' ) {
		timeo = 1000;
	}
	
	
	// chuẩn hóa đối tượng
	jd = jQuery.trim( jd );
	
	// nếu đầu vào không chỉ rõ là ID hay class -> mặc định là class
	var first_chart = jd.substr( 0, 1 );
	if ( first_chart != '.' && first_chart != '#' ) {
		jd = '.' + jd;
		
		// nếu là slider của Echbay Blog widget -> kiểm tra class đầu vào chuẩn chưa
		if ( jd.split('.widget-run-slider').length == 1 && jQuery( jd + ' li' ).length > 0 ) {
			// thiếu thì bổ sung thêm
			jd += ' .widget-run-slider';
		}
	}
	
	
	// căn chỉnh chiều cao cho slider theo LI đã
	if ( jQuery( jd + ' li' ).length > 0 ) {
		if ( typeof _global_js_eb != 'undefined' ) {
			_global_js_eb.auto_margin();
			
			//
//			console.log( jQuery( jd + ' li:first' ).height() );
//			jQuery( jd ).height( jQuery( jd + ' li:first' ).height() ).addClass('jEBE_slider-position');
			jQuery( jd ).height( jQuery( jd + ' li:first' ).height() );
		}
		
		// chỉ hiển thị những LI đầu tiên
		if ( typeof conf['visible'] == 'undefined' ) {
			conf['visible'] = 1;
		}
		if ( jQuery( jd + ' li' ).length > conf['visible'] ) {
			jQuery( jd + ' li' ).css({
				opacity: .01
			});
			
			jQuery( jd + ' li' ).slice( 0, conf['visible'] ).each(function() {
				jQuery( this ).css({
					opacity: 1
				});
			});
		}
		
		if ( typeof conf['size'] != 'undefined' && conf['size'].split('x').length > 1 ) {
			var conf_size = conf['size'].split('x');
			conf['size'] = conf_size[1] + '/' + conf_size[0];
		}
		
		// chỉnh lại chiều cao cho LI
		if ( timeo > 200 ) {
			setTimeout(function () {
				jQuery( jd ).height( jQuery( jd + ' li:first' ).height() );
//			}, timeo/ 3 * 2 );
			}, 200 );
		}
		
		//
		if ( timeo > 500 ) {
			setTimeout(function () {
				jQuery( jd ).height( jQuery( jd + ' li:first' ).height() );
//			}, timeo/ 3 * 2 );
			}, 500 );
		}
	}
	
	// rồi hẹn giờ tạo slider
	setTimeout(function () {
		jQuery( jd + ' li' ).css({
			opacity: 1
		});
		
		jEBE_slider ( jd, conf, callBack );
	}, timeo );
	
}

function jEBE_multi_slider ( jd, conf, callBack ) {
	
	if ( typeof conf != 'object' ) {
		conf = {};
	}
	
	//
	if ( typeof callBack != 'function' ) {
		callBack = null;
	}
	
	jd = jd.split( ',' );
	
	for ( var i = 0; i < jd.length; i++ ) {
		jEBE_slider ( jQuery.trim( jd[i] ), conf, callBack );
	}
	
}

function jEBE_slider ( jd, conf, callBack, slider_reload ) {
	
	// nếu có function kết thúc phiên làm việc -> xử lý luôn
	if ( typeof callBack != 'function' ) {
		callBack = null;
	}
	
	// xem có phải là load lại slider hay không, nếu có -> không gọi lại 1 số chức năng
	if ( typeof slider_reload == 'undefined' ) {
		slider_reload = false;
	}
	
	// kiểm tra và nạp jQuery
	if ( typeof jQuery != 'function' ) {
		console.log('jQuery not found');
		return false;
	}
	if ( typeof $ != 'function' ) {
		$ = jQuery;
	}
	
	//
	if ( typeof jEBE_slider_cache_option[jd] == 'undefined' ) {
		jEBE_slider_cache_option[jd] = {};
	}
	
	//
	if ( typeof jd == 'undefined' || jd == '' || jQuery(jd).length == 0 ) {
		if ( cf_tester_mode == 1 ) console.log( 'jEBE_slider! ' + jd + ' not found' );
		return false;
	}
	if ( cf_tester_mode == 1 ) console.log('jEBE_slider! Create slider ' + jd);
	var jd_class = 'child-' + jd.substr( 1 ).replace( /\.|\#|\s/g, '-' );
	var jd_to_class = '.' + jd_class;
	
	
	if ( typeof conf != 'object' ) {
		conf = {};
	}
	
	
	// config mặc định
	var set_default_conf = function ( k, v ) {
		if ( typeof conf[k] == 'undefined' ) {
			conf[k] = v;
		}
	};
	var get_thumbnail = function ( img ) {
		if ( typeof ___eb_set_img_to_thumbnail == 'function' ) {
			return ___eb_set_img_to_thumbnail( img );
		}
		return img;
	};
	var remove_thumbnail = function ( img ) {
		if ( typeof ___eb_set_thumb_to_fullsize == 'function' ) {
			return ___eb_set_thumb_to_fullsize( img );
		}
		return img;
	};
	
	// mặc định là ẩn nếu không có LI nào
	set_default_conf( 'hide_if_null', true );
	set_default_conf( 'for_class', 'li' );
	
	// kích thước
	set_default_conf( 'width', 1 );
	set_default_conf( 'height', 1 );
	// tỷ lệ giữa chiều cao và chiều rộng
	set_default_conf( 'size', conf['height'] + '/' + conf['width'] );
	
	// tự động chạy
	set_default_conf( 'autoplay', false );
	
	// tắt chế độ tự động chạy khi trong soạn thảo
	if ( top != self || window.location.href.split('?elementor-preview=').length > 1 ) {
		conf['autoplay'] = false;
	}
	
	// bật touch
	set_default_conf( 'swipemobile', false );
	// tốc độ chuyển slide ( mini giây )
	set_default_conf( 'speed', 0 );
	if ( conf['speed'] > 0 ) {
		conf['speed'] = conf['speed']/ 1000;
	}
	// giãn cách chuyển slide (mini giây)
	set_default_conf( 'speedNext', 5000 );
	
	// nút bấm chuyển ảnh
	set_default_conf( 'buttonListNext', true );
	
	// thumbnail -> vì là lấy hình ảnh làm thumbnail -> cần class chứa URL ảnh (src hoặc data-img)
	set_default_conf( 'thumbnail', false );
	set_default_conf( 'thumbnailSlider', true );
	// kích thước của thumbnail
	set_default_conf( 'thumbnailWidth', 90 );
	set_default_conf( 'thumbnailHeight', conf['thumbnailWidth'] );
	
	// Số LI hiển thị một lúc
	set_default_conf( 'visible', 1 );
	set_default_conf( 'visible_mobile', conf['visible'] );
	// chỉnh lại số LI hiển thị trên mobile
	if ( jQuery(window).width() < 768 ) {
		conf['visible'] = conf['visible_mobile'];
	}
	set_default_conf( 'scrollNum', conf['visible'] );
	
	// Bấm chuyển ảnh trên slider
	set_default_conf( 'sliderArrow', false );
	// nút bấm
	set_default_conf( 'sliderArrowLeft', 'fa-angle-left' );
	set_default_conf( 'sliderArrowRight', 'fa-angle-right' );
	// font-size
	set_default_conf( 'sliderArrowSize', 30 );
	// Kích thước nút bấm slider
	set_default_conf( 'sliderArrowWidthLeft', 'auto' );
	set_default_conf( 'sliderArrowWidthRight', 'auto' );
	
	set_default_conf( 'showRandom', false );
	set_default_conf( 'version', '1.0.0' );
	
	// conf['sliderArrow']
	if ( cf_tester_mode == 1 ) {
		console.log( jd );
		console.log( conf );
	}
	
	
	// kiểm tra có li nào ở trong không
	var len = jQuery(jd + ' ' + conf['for_class']).length || 0;
//	console.log( len );
	if ( len == 0 ) {
		if ( conf['hide_if_null'] == true ) {
			jQuery(jd).hide();
		}
		if ( cf_tester_mode == 1 ) console.log( 'slider has been STOP by LI length it zero' );
		return false;
	}
	
	
	// chiều cao cho slide
	var wit = jQuery(jd).width(),
		hai = '';
	
	// thêm chức năng cho chiều cao tự động (auto)
	if ( conf['size'] == '' || conf['size'] == 'li' ) {
		hai = Math.ceil( jQuery(jd + ' li:first').height() ) - 1;
		
		//
		if ( conf['size'] == 'li' ) {
			conf['size'] = hai + '/' + ( Math.ceil( jQuery( jd + ' li:first').width() ) - 1 );
			console.log('LI to size: ' + conf['size']);
		}
	}
	//
	else if ( conf['size'] == 'auto' ) {
		// Nếu có class auto resize -> trước đó bị hàm khác chặn mất rồi -> add lại class mới để xử lý
		if ( jQuery(jd + ' .auto-size').length > 1 ) {
			jQuery(jd + ' .auto-size').addClass('ti-le-global').removeClass('auto-size');
		}
		
		// Nếu có nhiều hơn 1 ảnh -> tìm size thật
		if ( jQuery(jd + ' .ti-le-global').length > 1 ) {
			// xóa class để không cho nó còn được resize nữa
			jQuery(jd + ' .ti-le-global').addClass('ti-le-global-xoa').removeClass('ti-le-global');
			
			// lấy ảnh đầu tiên của slider -> ưu tiên ảnh mobile trước -> load cho nhẹ và nhanh
			var get_img_size = jQuery(jd + ' li:first .ti-le-global').attr('data-mobile-img') || jQuery(jd + ' li:first .ti-le-global').attr('data-table-img') || jQuery(jd + ' li:first .ti-le-global').attr('data-img') || '';
			console.log(get_img_size);
			
			//
			var new_jd = jd.replace( /\.|\#/g, '_' );
			jQuery(jd + ' li:first .ti-le-global').html('<img src="' + get_img_size + '" id="' + new_jd + '" width="' + wit + '" data-class="' + jd + '" />');
			
			jQuery('#' + new_jd).on('load', function () {
				console.log('TESTTTTTTTTTTTTTTTTTTTTT! add function for slider');
			});
			
//			jEBE_slider ( jd, conf, callBack );
		}
		
		// thoát luôn
		if ( cf_tester_mode == 1 ) console.log( 'slider has been RETURN size = auto' );
		
		//
		if ( typeof callBack == 'function' && callBack != null ) {
			if ( cf_tester_mode == 1 ) console.log(' call to callBack function before return');
			callBack();
		}
		
		//
		return false;
	}
	// lấy theo kích thước màn hình
	else if ( conf['size'] == 'full' ) {
		hai = jQuery(window).height();
		
		// thêm class khẳng định full size theo màn hình
		jQuery(jd).addClass('slider-window-size');
	}
	// tính toán chiều cao từ tỷ lệ của chiều rộng
	else {
		hai = Math.ceil(wit * eval( conf['size'] )/ conf['visible']) - 1;
	}
	
	set_default_conf( 'lineHeight', hai + 'px' );
	
	jQuery(jd).height( hai ).attr({
		'data-size' : conf['size']
	}).css({
		'line-height' : conf['lineHeight']
	});
	
	// chỉ có 1 ảnh -> thoát
//	if ( len == 1 ) {
	if ( len <= conf['visible'] ) {
		if ( cf_tester_mode == 1 ) console.log( 'slider has been STOP by LI length < config visible' );
		return false;
	}
	
	//
	/*
	jQuery(window).resize(function(e) {
		// chỉnh lại chiều cao cho slide
		jQuery(jd).height( hai ).css({
			'line-height' : conf['lineHeight']
		});
	});
	*/
	
	
	//
	if ( conf['thumbnail'] != false ) {
		// nếu lệnh set thumbnail là true -> chuyển thành html tag để lấy dữ liệu
		if ( conf['thumbnail'] == true ) {
			conf['thumbnail'] = 'ul li';
		}
		if ( cf_tester_mode == 1 ) console.log( 'GET thumbnail with ' + jd + ' ' + conf['thumbnail'] );
		
		//
		var str_btn = '',
			i = 0;
		jQuery(jd + ' ' + conf['thumbnail']).each(function() {
			var img = jQuery(this).attr('data-img') || jQuery(this).attr('data-src') || jQuery(this).attr('src') || jQuery('.banner-ads-media', this).attr('data-img') || '';
			
			//
			if ( img != '' ) {
				// nếu chiều dài và rộng của thumbnail giống nhau -> lấy theo thumb
				if ( conf['thumbnailWidth'] * 1 == conf['thumbnailHeight'] * 1 ) {
					img = get_thumbnail( img );
				}
				
				str_btn += '<li data-i="' +i+ '" data-src="' + img + '"><div style="background-image: url(\'' + img + '\');">&nbsp;</div></li>';
				
				i++;
			}
		});
		
		//
		if ( str_btn == '' ) {
			console.log('thumbnail for ' + jd + ' not found!');
		}
		else {
			jQuery(jd).after('<div class="' + jd_class + '"><div class="jEBE_slider-thumbnail"><ul class="cf">' + str_btn + '</ul></div></div>');
			
			// Tạo slider cho thumbnail
			if ( conf['thumbnailSlider'] == true ) {
				var j_id = '_' + Math.random().toString(32).replace('.', '_');
				
				jQuery(jd_to_class + ' .jEBE_slider-thumbnail').attr({
					id: j_id
				});
//				console.log(Math.random());
				
				/*
				// các option mặc định thì chuyển về false hết
				jEBE_slider( '#' + j_id, {
					visible: 4,
					buttonListNext: false,
					size : conf['thumbnailHeight'] + '/' + conf['thumbnailWidth']
				}, function () {
				});
				*/
				
				//
				jQuery('#' + j_id).addClass('jEBE_slider-child-thumbnail').height( conf['thumbnailHeight'] )
				/*
				.css({
					height: conf['thumbnailHeight'] + 'px'
				})
				*/
				;
				
				// mặc định là hiển thị 4 ảnh con, nếu nhiều hơn 4 ảnh -> hiển thị dưới dạng slide
				if ( jQuery('#' + j_id + ' li').length > 4 ) {
					
					// set chiều rộng mới cho UL, để tất cả các LI sẽ nằm trên 1 dòng
					jQuery('#' + j_id + ' ul').attr({
						'data-width': jQuery('#' + j_id + ' ul').width()
					}).width( jQuery('#' + j_id + ' li').length * ( jQuery('#' + j_id + ' ul').width()/ 4 ) );
					
					// tính toán chiều rộng cho các thẻ li
					jQuery('#' + j_id + ' li').width( ( 100/ jQuery('#' + j_id + ' li').length - 0.1 ) + '%' );
					
					//
					var str_for_thumb_row = '',
						j_id_left = j_id + '_left',
						j_id_right = j_id + '_right';
					
					str_for_thumb_row += '<div id="' + j_id_left + '" class="jEBE_slider-left-thumbnail ' + j_id + '-thumb-left"><i class="fa fa-angle-left"></i></div>';
					str_for_thumb_row += '<div id="' + j_id_right + '" class="jEBE_slider-right-thumbnail ' + j_id + '-thumb-right"><i class="fa fa-angle-right"></i></div>';
					
					//
					jQuery('#' + j_id).addClass('jEBE_slider-scroll-thumbnail').before('<div class="jEBE_slider-arrow-thumbnail ' + j_id + '-thumb-arrow">' + str_for_thumb_row + '</div>');
					
					//
					jQuery('#' + j_id_left + ', #' + j_id_right).height( conf['thumbnailHeight'] ).css({
						'line-height': conf['thumbnailHeight'] + 'px'
					});
					
					//
					jQuery('#' + j_id_left).off('click').click(function () {
						var a = jQuery('#' + j_id + ' ul').attr('data-scroll') || 0;
						a = a - 1;
						if ( a < 0 ) {
							a = 0;
						}
						//console.log('aaaaaaaaaaaaaaaaaaa: ' + a);
						
						jQuery('#' + j_id + ' ul').attr({
							'data-scroll': a
						}).css({
//							left: '-' + jQuery('#' + j_id + ' ul').attr('data-width') + 'px'
							left: '-' + ( a * 100 ) + '%'
						});
					});
					
					//
					jQuery('#' + j_id_right).off('click').click(function () {
//						console.log(Math.random());
						var a = jQuery('#' + j_id + ' ul').attr('data-scroll') || 0,
							max_li = jQuery('#' + j_id + ' li').length/ 4;
						a = a - (0 - 1);
//						console.log(a);
//						console.log(max_li);
						if ( a > max_li ) {
//							a = max_li;
							a = 0;
						}
//						console.log(a);
						
						jQuery('#' + j_id + ' ul').attr({
							'data-scroll': a
						}).css({
//							left: '-' + jQuery('#' + j_id + ' ul').attr('data-width') + 'px'
							left: '-' + ( a * 100 ) + '%'
						});
					});
					
				}
				else {
					jQuery('#' + j_id + ' li').width( '25%' );
				}
			}
			
			// tạo css cho thumbmail
			jQuery(jd_to_class + ' .jEBE_slider-thumbnail div').width( conf['thumbnailWidth'] ).height( conf['thumbnailHeight'] )
			/*
			.css({
				width: conf['thumbnailWidth'] + 'px',
				height: conf['thumbnailHeight'] + 'px'
			})
			*/
			;
		}
	}
	
	
	// cái này phải nằm sau lệnh thumbnail thì nó mới lên trước được
	if ( conf['buttonListNext'] == true ) {
		var str_btn = '',
			listBtnLen = ( conf['visible'] > 1 ) ? Math.ceil( len/ conf['visible'] ) : len;
		for ( var i = 0; i < listBtnLen; i++ ) {
			str_btn += '<li data-i="' +i+ '"><i class="fa fa-circle"></i></li>';
		}
		jQuery(jd).after('<div class="' + jd_class + '"><div class="big-banner-button"><ul>' + str_btn + '</ul></div></div>');
	}
	
	// tạo css cho slider
	jQuery(jd).addClass('jEBE_slider-position');
	// tạo các phiên bản chuyển động khác
	if ( conf['version'] != '1.0.0' ) {
		// lấy 1 phiên bản ngẫu nhiên (code hỗ trợ đến đâu thì tăng max tới đó)
		if ( conf['version'] == 'rand' ) {
			conf['version'] = g_func.getRandomInt( 1, 11 );
		}
		
		//
		jQuery(jd).addClass( 'jEBE_slider-position' + conf['version'].toString() );
	}
	
	/*
	jQuery(jd).css({
		position: 'relative',
		overflow: 'hidden'
	});
	*/
	
	
	// hiệu ứng khi click vào thẻ LI
	if ( slider_reload == false ) {
		
		jQuery(jd + ' ul').width( ( 100 * len/ conf['visible'] ) + '%' );
		if ( conf['speed'] > 0 ) {
			jQuery(jd + ' ul').css({
				'-moz-transition': 'all ' + conf['speed'] + 's ease',
				'-o-transition': 'all ' + conf['speed'] + 's ease',
				'-webkit-transition': 'all ' + conf['speed'] + 's ease',
				transition: 'all ' + conf['speed'] + 's ease'
			});
		}
		
		jQuery(jd + ' li').css({
	//		width: ( 100/ len/ conf['visible'] ) + '%',
			width: ( 100/ len ) + '%'
		});
		
		
		//
		var  i = 0,
			// nếu slider đầu tiên mà là video -> xử lý khác đi chút
			first_this_video = false,
			// ID của phần video
			first_this_id_video = '',
			check_and_reload_video = false;
		
		jQuery(jd + ' li').each(function() {
			jQuery(this).attr({
				'data-i' : i
			});
			
			// Kiểm tra xem slide đầu tiên có phải là video không
			if ( i == 0 ) {
//				var vd = jQuery('div.banner-ads-media', this).attr('data-video') || '';
				var vd = jQuery('div.banner-ads-media', this).attr('data-ytb-url') || '';
				
				if ( vd != '' ) {
//					vd = vd.split('.');
//					vd = vd[ vd.length - 1 ];
					if ( vd.split('youtube.com').length > 1
					|| vd.split('.mp4').length > 1
					|| vd.split('.m4v').length > 1 ) {
						first_this_video = true;
						first_this_id_video = 'video_' + jd.replace( /\.|\#\-/g, '_' ) + i;
						
						// kiểm tra và load lại đối với 1 số video (không phải youtube là được)
						if ( vd.split('youtube.com').length == 1 ) {
							check_and_reload_video = true;
						}
					}
				}
			}
			
			//
			i += 1;
		}).off('click').click(function () {
//			console.log( Math.random() );
			
			var i = jQuery(this).attr('data-i') || 0,
				video_id = 'video_' + jd.replace( /\.|\#\-/g, '_' ) + i;
			if ( conf['scrollNum'] != conf['visible'] ) {
				/*
				console.log( 'i * scrollNum' );
				console.log( i );
				console.log( conf['scrollNum'] );
				console.log( i * conf['scrollNum'] );
				console.log( i * conf['scrollNum'] + conf['visible'] * 1 );
				console.log( 'END i * scrollNum' );
				*/
				if ( i > 0 && i * conf['scrollNum'] + conf['visible'] * 1 > jQuery(jd + ' li').length ) {
					i = 0;
				}
			}
			else {
				if ( i * conf['visible'] > jQuery(jd + ' li').length ) {
					i = 0;
				}
			}
			
			//
			var w = jQuery(jd).width(),
				scroll_width = 0 - i * 100;
			
			if ( conf['scrollNum'] != conf['visible'] ) {
				if ( conf['scrollNum'] < conf['visible'] ) {
					scroll_width = conf['scrollNum']/ conf['visible'] * 100;
					scroll_width = 0 - i * scroll_width;
//					scroll_width = scroll_width.toString() + 'px';
				}
			}
			/*
			else {
				scroll_width = scroll_width.toString() + '%';
			}
			*/
//			console.log( scroll_width );
			
			jQuery(jd + ' ul').css({
				left: scroll_width.toString() + '%'
//				left: scroll_width
//				left: ( 0 - i * w ) + 'px'
//				left: ( 0 - i * 100/ conf['visible'] ) + '%'
			});
			
			jQuery(jd)
//			.scrollLeft(0)
			.attr({
				'data-i' : i,
//				'data-scroll' : i * jQuery(jd).width()
				'data-scroll' : i * w
			});
			
			jEBE_slider_dang_scroll = false;
			
			//
			jQuery(jd + ' li, .' + jd_class + ' li').removeClass('selected');
			jQuery(jd + ' li[data-i="' + i + '"], .' + jd_class + ' li[data-i="' + i + '"]').addClass('selected');
			
			
			
			// kiểm tra xem có video không -> có thì tự phát video thôi
//			var vd = jQuery('div.banner-ads-media', this).attr('data-video') || '';
			var vd = jQuery('div.banner-ads-media', this).attr('data-ytb-url') || '';
//			console.log(vd);
			
			// xóa các video trong cùng slide
//			jQuery(jd + ' .banner-video-media').html('&nbsp;');
			jQuery(jd + ' .banner-youtube-video').html('&nbsp;');
			
			// tạm dừng các video đang phát
			jQuery('.banner-mp4-video video').each(function () {
				var video_id = jQuery(this).attr('id') || '';
				
				if ( video_id != '' ) {
					dog(video_id).pause();
					jQuery(this).hide();
					
					//
					console.log('Pause video #' + video_id);
				}
				else {
					jQuery(this).get(0).pause();
				}
			});
			
			//
			if ( vd.split('youtube.com').length > 1 ) {
				vd = jQuery('div.banner-ads-media', this).attr('data-ytb-uri') || '';
				vd = _global_js_eb.youtube_id( vd, 1 );
//				console.log( vd );
				if ( vd != '' ) {
					if ( vd.split('&').length > 1 ) {
						vd = vd.replace( '&', '?' );
						vd += '&';
					}
					else {
						vd += '?';
					}
//					console.log( vd );
					
					// xóa thẻ a
					jQuery('a', this).hide();
					
					// tính toán chiều rộng để tạo video
					var h = jQuery('div.banner-ads-media', this).height() || 0,
						w = jQuery('div.banner-ads-media', this).width() || 0,
						h_video = h,
						w_video = w;
//					console.log('W Slider: ' + w);
//					console.log('H Slider: ' + h);
					
					// nếu chiều rộng < chiều cao -> màn hình dọc
					if ( w < h ) {
						w_video = h * ( 560/ 315 );
						
						// test -> tính lại xem có đúng chiều cao không
//						console.log('TEST h: ' + w_video/ 560 * 315);
					}
					// màn hình ngang
					else {
						// tính chiều cao của video dựa theo chiều rộng, tỉ lệ youtube_video_default_size
						h_video = w * youtube_video_default_size;
					}
//					console.log('W Video: ' + w_video);
//					console.log('H Video: ' + h_video);
					
					//
					jQuery('div.banner-ads-media', this)
					.addClass('banner-video-media')
					.addClass('banner-youtube-video')
					.html('<iframe id="' + video_id + '" width="' + Math.ceil( w_video ) + '" height="' + Math.ceil( h_video ) + '" src="https://www.youtube.com/embed/' + vd + 'rel=0&autoplay=1&mute=1&html5=1&controls=0&loop=1&enablejsapi=1&origin=' + decodeURIComponent( web_link ) + '&widgetid=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>');
				}
			}
			else if ( vd.split('.mp4').length > 1
			|| vd.split('.m4v').length > 1 ) {
				// xóa thẻ a
//				jQuery('a', this).hide();
				
				// tính toán chiều rộng để tạo video
				var h = jQuery('div.banner-ads-media', this).height() || 0,
					w = jQuery('div.banner-ads-media', this).width() || 0,
					h_video = '100%',
					w_video = '100%';
//				console.log('W Slider: ' + w);
//				console.log('H Slider: ' + h);
				
				// nếu chiều rộng < chiều cao -> màn hình dọc
				if ( w < h ) {
					w_video = Math.ceil( h * ( 16/ 9 ) );
					
					// test -> tính lại xem có đúng chiều cao không
//					console.log('TEST h: ' + w_video/ 16 * 9);
				}
				// màn hình ngang
				else {
					// tính chiều cao của video dựa theo chiều rộng, tỉ lệ 16:9
	//				h_video = parseInt( w_video/ 16 * 9, 10 );
					h_video = Math.ceil( w * ( 9/ 16 ) );
				}
//				console.log('W Video: ' + w_video);
//				console.log('H Video: ' + h_video);
				
				
				// nếu đã có video -> chỉ gọi play, không cần inner lại
				if ( jQuery('div.banner-mp4-video', this).length > 0 ) {
					// play video này lên
					dog(video_id).play();
					jQuery('#' +video_id)
					.show()
					.width( w_video )
					.height( h_video )
					.attr({
						width : w_video,
						height : h_video
					});
					
					//
					console.log('Play video #' + video_id);
					
					// trả về luôn
					return true;
				}
				
				
				// tạo video
				// https://www.w3schools.com/howto/howto_css_fullscreen_video.asp
				jQuery('div.banner-ads-media', this)
				.addClass('banner-video-media')
				.addClass('banner-mp4-video')
				// mẫu html mặc định
				/*
				.html('<video width="' + w_video + '" height="' + h_video + '" autoplay muted loop preload="auto">\
					<source src="' + vd + '" type="video/mp4">\
				</video>')
				*/
				// làm theo mẫu của rolls-royce (có poster)
				/*
				.html('<video width="' + w_video + '" height="' + h_video + '" data-player="" data-embed="default" poster="' + ( jQuery('div.banner-ads-media', this).attr('data-mobile-img') || '' ) + '" tabindex="-1" autoplay muted loop preload="true" src="' + vd + '" playsinline="playsinline">\
					<track kind="metadata" label="segment-metadata">\
				</video>')
				*/
				// không poster
				.html('<video id="' + video_id + '" width="' + w_video + '" height="' + h_video + '" data-player="" data-embed="default" tabindex="-1" autoplay muted loop preload="true" src="' + vd + '" playsinline="playsinline">' +
					'<track kind="metadata" label="segment-metadata">' +
				'</video>');
				
				// tự động play -> thay thế cho attr autoplay ở trên, có vẻ cũng không khả thi
//				dog(video_id).play();
			}
			
			//
			/*
			setTimeout(function () {
				jEBE_slider_cache_option[jd]['scroll_runing'] = false;
			}, 200);
			*/
			
		});
	}
	
	//
	var first_click = 0;
	if ( conf['showRandom'] == true ) {
		first_click = g_func.rand( 0, jQuery(jd + ' li').length - 1 );
		console.log('Random node: ' + first_click);
	}
	
	// video ở đầu -> load chậm lại chút -> do chưa kịp định khung
//	console.log(first_this_video);
	if ( slider_reload == false ) {
		if ( first_this_video == true ) {
			jQuery(document).ready(function() {
//			jQuery(window).on('load', function () {
//				alert( Math.random() );
				jQuery(jd + ' li[data-i="0"]').click();
				
				//
				if ( check_and_reload_video == true ) {
					setTimeout(function () {
						// kiểm tra video đã được play chưa -> đang bị lỗi autoplay trên chroem mobile
//						if ( dog(first_this_id_video).playing ) {
						if ( dog(first_this_id_video).currentTime > 0 ) { }
						// vẫn chưa được bật -> kích hoạt lại
						else {
//							jQuery( '#bigbanner-top1' ).before( first_this_id_video + '<br>' );
//							jQuery( '#bigbanner-top1' ).before( dog(first_this_id_video).src + '<br>' );
							
							//
//							dog(first_this_id_video).play();
//							jEBE_slider ( jd, conf, callBack, true );
							jQuery(jd + ' li[data-i="0"]').click();
							
							// thử kiểm tra lại lần nữa, trên iphone vẫn thấy được
							setTimeout(function () {
//								jQuery( '#bigbanner-top1' ).before( dog(first_this_id_video).currentTime + '<br>' );
								
								//
								if ( dog(first_this_id_video).currentTime > 0 ) { }
								else {
									// không được thì đành mở tab số 2 vậy
									if ( jQuery(jd + ' li[data-i="1"]').length > 0 ) {
										jQuery(jd + ' li[data-i="1"]').click();
									}
									// ko có tab 2 thì đành click lại tab 1
									else {
										jQuery(jd + ' li[data-i="0"]').click();
									}
								}
							}, 2500);
						}
//						jQuery( '#bigbanner-top1' ).before( dog(first_this_id_video).currentTime + '<br>' );
					}, 2500);
					
					//
					/*
					setInterval(function () {
						console.log( dog(first_this_id_video).currentTime );
					}, 500);
					*/
				}
			});
		}
		else {
			// nếu loạt bấm đầu tiên không phải cái số 0 -> ẩn tạm đi, để che mắt người dùng
			if ( first_click > 0 ) {
				jQuery(jd).css({
					opacity: .01
				});
				
				//
				setTimeout(function () {
					jQuery(jd).animate({
						opacity: 1
					});
				}, conf['speed'] * 1000 + 500 );
			}
//			console.log('First click: ' + first_click);
			
			//
//			setTimeout(function () {
				jQuery(jd + ' li[data-i="' + first_click + '"]').click();
//			}, 200);
		}
	}
	else {
		var current_li_show = jQuery(jd).attr('data-i') || '';
		if ( current_li_show != '' ) {
			jQuery(jd + ' li[data-i="' + current_li_show + '"]').click();
			console.log( 'current li show: ' + current_li_show );
		}
	}
//	return false;
	
	
	//
	jQuery('.' + jd_class + ' li').off('click').click(function () {
		var i = jQuery(this).attr('data-i') || 0;
//		console.log(i);
//		console.log(jd);
		
//		if ( jQuery(jd + ' li[data-i="' + i + '"]').length == 0 ) {
//		if ( i >= jQuery(jd + ' li').length ) {
//			i = 0;
//		}
		
		jQuery(jd + ' li[data-i="' + i + '"]').click();
		
		// tắt auto play
//		if ( jEBE_slider_cache_option[jd]['autoplay'] == true ) {
			jEBE_slider_cache_option[jd]['autoplay'] = false;
//			console.log('Stop autoplay for ' + jd);
//		}
	});
	
	
	//
	if ( slider_reload == false ) {
		if ( conf['autoplay'] == true ) {
			jEBE_slider_cache_option[jd]['autoplay'] = true;
			/*
			jEBE_slider_cache_option[jd] = {
				autoplay: true
			};
			*/
			
			//
			setTimeout(function () {
				setInterval(function () {
//					console.log( Math.random() );
					if ( jEBE_slider_cache_option[jd]['autoplay'] == true ) {
						var i = jQuery(jd).attr('data-i') || 0;
						i -= -1;
//						i -= 0 - conf['visible'];
//						console.log(i);
//						console.log(jd);
						
//						if ( jQuery(jd + ' li[data-i="' + i + '"]').length == 0 ) {
						if ( i >= jQuery(jd + ' li').length ) {
							i = 0;
						}
						
						jQuery(jd + ' li[data-i="' + i + '"]').click();
					}
				}, conf['speedNext'] );
			}, conf['speedNext'] );
		} else {
			jEBE_slider_cache_option[jd]['autoplay'] = false;
			/*
			jEBE_slider_cache_option[jd] = {
				autoplay: false
			};
			*/
		}
	}
	
	
	//
	if ( conf['sliderArrow'] == true && len > conf['visible'] ) {
		jQuery(jd).before('<div class="' + jd_class + '"><div class="jEBE_slider-toCenter"><div class="jEBE_slider-toLeft div-valign-center"><i class="fa ' + conf['sliderArrowLeft'] + '"></i></div> <div class="jEBE_slider-toRight div-valign-center text-right"><i class="fa ' + conf['sliderArrowRight'] + '"></i></div></div></div>');
		
		
		//
		jQuery(jd_to_class + ' .jEBE_slider-toLeft').off('click').click(function () {
			var i = jQuery(jd).attr('data-i') || 0;
			i -= 1;
//			i -= conf['visible'];
//			console.log(i);
//			console.log(jd);
			
			if ( i < 0 ) {
				i = jQuery(jd + ' li').length - 1;
			}
			
			jQuery(jd + ' li[data-i="' + i + '"]').click();
			
			//
			jEBE_slider_cache_option[jd]['autoplay'] = false;
		});
		
		jQuery(jd_to_class + ' .jEBE_slider-toRight').off('click').click(function () {
			var i = jQuery(jd).attr('data-i') || 0;
			i -= -1;
//			i -= 0 - conf['visible'];
			//console.log(i);
//			console.log(jd);
			
//			if ( jQuery(jd + ' li[data-i="' + i + '"]').length == 0 ) {
			if ( i * conf['visible'] >= jQuery(jd + ' li').length ) {
				i = 0;
			}
			//console.log(jQuery(jd + ' li').length);
			
			jQuery(jd + ' li[data-i="' + i + '"]').click();
			
			//
			jEBE_slider_cache_option[jd]['autoplay'] = false;
		});
		
		// tạo css cho nút next
		//console.log(conf['lineHeight']);
		jQuery( jd_to_class + ' .jEBE_slider-toLeft, ' + jd_to_class + ' .jEBE_slider-toRight' ).css({
			//'line-height' : conf['lineHeight'],
			'font-size': conf['sliderArrowSize'] + 'px'
		}).height( hai );
		
		// tạo nút bấm chuyển ảnh ngay trên khung ảnh nếu:
		// người dùng đang xem trên màn ảnh rộng
		// hiển thì mỗi ảnh 1 cái
		if ( global_window_width > 750 || conf['visible'] == 1 ) {
			if ( conf['sliderArrowWidthLeft'] != '' ) {
				jQuery( jd_to_class + ' .jEBE_slider-toLeft' ).css({
					'width': conf['sliderArrowWidthLeft']
				});
			}
			
			if ( conf['sliderArrowWidthRight'] != '' ) {
				jQuery( jd_to_class + ' .jEBE_slider-toRight' ).css({
					'width': conf['sliderArrowWidthRight']
				});
			}
		}
		
		//
		if ( cf_tester_mode == 1 ) {
			console.log(jd);
			console.log(jd_to_class);
			console.log(slider_reload);
//			console.log(conf['swipemobile']);
//			console.log(global_window_width);
		}
		
		
		// sử dụng swipe để chuyển ảnh
		// https://github.com/mattbryson/TouchSwipe-Jquery-Plugin
		// http://labs.rampinteractive.co.uk/touchSwipe/demos/Basic_swipe.html
		if ( slider_reload == false && conf['swipemobile'] == true && global_window_width < 750 ) {
//			console.log('jEBE_swipe_slider');
//			setTimeout(function () {
			
			jQuery(window).on('load', function () {
//				console.log('jEBE_swipe_slider');
				jEBE_swipe_slider( jd, jd_to_class, conf );
			});
			
			jQuery(document).ready(function() {
				jEBE_swipe_slider( jd, jd_to_class, conf );
			});
			
//			}, 3000);
		}
		
	}
	
	
	//
	if ( typeof callBack == 'function' && callBack != null ) {
		if ( cf_tester_mode == 1 ) console.log(' call to callBack function');
		callBack();
	}
	
	
	
	// mỗi khi người dùng thay đổi kích thước trình duyệt
//	if ( slider_reload == false ) {
	if ( slider_reload == false && global_window_width < 1100 ) {
		jQuery(window).resize(function(e) {
//			console.log( jd_class );
//			console.log( conf );
			
			// xóa các class liên quan của slider này đí
			jQuery( jd_to_class ).remove();
			
			// căn chỉnh lại chiều cao của banner, để quá trình định hình thực hiện lại từ đầu
			/*
			jQuery( jd + ' .ti-le-global' ).height('auto').css({
				'line-height': 'normal'
			});
			*/
			_global_js_eb.auto_margin();
			
			// nạp lại slider
			jEBE_slider ( jd, conf, callBack, true );
		});
	}
	
}


function jEBE_swipe_slider ( jd, jd_to_class, conf ) {
	try {
		if ( typeof jEBE_active_swipe_slider[ jd ] != 'undefined' && jEBE_active_swipe_slider[ jd ] == true ) {
			if ( cf_tester_mode == 1 ) console.log( 'Swipe runing... ' + jd );
			return false;
		}
		jEBE_active_swipe_slider[ jd ] = true;
		
		//
//		var str_process = jd;
//		var str_process = jd_to_class + ' .jEBE_slider-toLeft, ' + jd_to_class + ' .jEBE_slider-toRight';
//		var str_process = jd + ' ul li a, ' + jd_to_class + ' .jEBE_slider-toLeft, ' + jd_to_class + ' .jEBE_slider-toRight';
		
		// swipe trên nút chuyển ảnh
		var str_process = jd_to_class + ' .jEBE_slider-toLeft, ' + jd_to_class + ' .jEBE_slider-toRight';
		// nếu là blog hoặc ads -> swipe trên banner luôn
		if ( jQuery( jd + ' .echbay-blog-left' ).length > 0 ) {
			str_process = jd + ' .echbay-blog-left, ' + jd + ' .banner-ads-media';
		}
		
		//
		if ( cf_tester_mode == 1 ) {
			console.log( 'Swipe mobile for ' + jd + ', ' + jd_to_class );
			console.log( 'Config of swipe:' );
			console.log( conf );
			console.log( 'Process: ' + str_process );
		}
		
		//
		jQuery( jd ).addClass('jEBE_swipe_slider');
		
		//
		jQuery( str_process ).swipe( {
			// Generic swipe handler for all directions
			swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
//				console.log( direction );
				
				//
				if ( direction == 'left' ) {
//				if ( direction == 'left' || direction == 'up' ) {
//					jQuery(jd_to_class + ' .jEBE_slider-toRight').click();
					
					//
					var i = jQuery( jd ).attr('data-i') || '';
					if ( i != '' ) {
						i++;
						if ( i >= jQuery( jd + ' li' ).length ) {
							i = 0;
						}
//						console.log(i);
						jQuery( jd + ' li[data-i="' + i + '"]').click();
						
						//
						jEBE_slider_cache_option[jd]['autoplay'] = false;
					}
				}
				else if ( direction == 'right' ) {
//				else if ( direction == 'right' || direction == 'down' ) {
//				else {
//					jQuery(jd_to_class + ' .jEBE_slider-toLeft').click();
					
					//
					var i = jQuery( jd ).attr('data-i') || '';
					if ( i != '' ) {
						i--;
						if ( i < 0 ) {
							i = jQuery( jd + ' li' ).length - 1;
						}
//						console.log(i);
						jQuery( jd + ' li[data-i="' + i + '"]').click();
						
						//
						jEBE_slider_cache_option[jd]['autoplay'] = false;
					}
				}
				// hỗ trợ người dùng cuộn trang, do lệnh làm liệt chức năng cuộn trang ở đây
				else {
					// lấy scroll hiện tại
					var a = window.scrollY || jQuery(window).scrollTop(),
						w2 = jQuery(window).height()/ 2;
					
					//
					if ( direction == 'up' ) {
						a += w2;
					}
					else if ( direction == 'down' ) {
						a -= w2;
						if ( a < 0 ) {
							a = 0;
						}
					}
					
					//
					jQuery('body,html').animate({
						scrollTop: a
					}, 200);
				}
			},
			// Default is 75px, set to 0 for demo so any distance triggers swipe
			threshold: 0
		});
	} catch ( e ) {
		console.log( WGR_show_try_catch_err( e ) );
		
		//
//		jEBE_active_swipe_slider[ jd ] = false;
	}
}

