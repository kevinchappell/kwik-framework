<?php
/**
 * Widget Name: Latest Items
 * Description: A widget that allows you to display the entries from your blog
 * Version: 0.5
 *
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'kwik_latest_posts' );

/**
 * Register our widget.
 * 'Kwik_Latest_Posts_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function kwik_latest_posts() {
	register_widget( 'Kwik_Latest_Posts_Widget' );
}

/**
 *
 * @since 0.2
 */
class Kwik_Latest_Posts_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function Kwik_Latest_Posts_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_latest_posts', 'description' => esc_html__( 'The most recent posts with teaser text', 'kwik' ) );

		/* Widget control settings. */
		$control_ops = array( 'width' => 150, 'height' => 350, 'id_base' => 'latest-posts-widget' );

		/* Create the widget. */
		parent::__construct( 'latest-posts-widget', esc_html__( 'Kwik Latest Posts', 'kwik' ), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		$inputs = new KwikInputs;
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters( 'widget_title', $instance['title'] );
		$category_id = intval( $instance['category_id'] );
		$tag_id = $instance['tag_id'];
		$num_posts = absint( $instance['num_posts'] );
		$post_offset =  absint( $instance['post_offset'] );
		$delim = $instance['delim'];
		$neat = isset( $instance['neat'] ) ? $instance['neat'] : false;
		$excerpt_length = absint( $instance['excerpt_length'] );
		$read_more = isset( $instance['read_more'] ) ? $instance['read_more'] : false;
		$show_thumbs = isset( $instance['show_thumbs'] ) ? $instance['show_thumbs'] : false;
		$thumb_crop = isset( $instance['thumb_crop'] ) ? $instance['thumb_crop'] : false;
		$post_thumb_width = absint( $instance['post_thumb_width'] );
		$post_thumb_height = absint( $instance['post_thumb_height'] );
		$post_type = $instance['post_type'];
		$show_date =  $instance['show_date'];
		$date_style =  $instance['date_style'];
		$show_all_link = isset( $instance['show_all_link'] ) ? $instance['show_all_link'] : false;
		$show_all_text = $instance['show_all_text'];
		$show_all_text_position = isset( $instance['show_all_text_position'] ) ? $instance['show_all_text_position'] : 'top';
		$views_posts_link = '';

		if ( $show_all_link ){
			if ( 'page' === get_option( 'show_on_front' ) ) {
				$views_posts_link = get_permalink( get_option( 'page_for_posts' ) );
			} else {
				$views_posts_link = get_bloginfo( 'url' );
			}
			$views_posts_link = $inputs->markup( 'a', $show_all_text, array('class' => 'view_all', 'href' => $views_posts_link, 'title' => __('View All', 'kwik' ) ) );
		}

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) {
			echo $before_title;
			echo $title;
			if( $show_all_text_position === 'top' ) {
				echo $views_posts_link;
			}
			echo $after_title;
		}

		/* Display the Latest Items accordingly... */
		$args = array(
			'post_type' => $post_type,
			'post_status' => 'publish',
			'posts_per_page' => $num_posts,
			'ignore_sticky_posts' => 1,
			'offset' => $post_offset,
			);

		if ( $tag_id ){
			$args['tag'] = $tag_id;
		}

		if ( $category_id && 0 !== $category_id ){
			$args['cat'] = $category_id;
		}

		$current_post = array( get_the_ID() );
		if ( $current_post){
			$args['post__not_in'] = $current_post;
		}

	$latest_posts = null;
	$latest_posts = new WP_Query( $args);
	if ( $latest_posts->have_posts()) : ?>
	<div class="latest_posts">
	<ul>
	<?php	while( $latest_posts->have_posts()) : $latest_posts->the_post();
	update_post_caches( $posts);
	?>
	<li>
	<?php
	$category = get_the_category(get_the_ID());
	if ( has_post_thumbnail() && $show_thumbs !== false ) {
	$thumb = get_the_post_thumbnail( get_the_ID(), array( $post_thumb_width, $post_thumb_height));
	echo KwikInputs::markup( 'a', $thumb, array("href" => get_permalink(), "title" => get_the_title()));
	}
	?>
	<a title="<?php the_title(); ?>" href="<?php the_permalink() ?>"><?php the_title(); ?></a><br/>

	<?php if ( $excerpt_length != 0) echo '<div class="entry-summary">'.KwikUtils::neat_trim(get_the_excerpt(), $excerpt_length, $delim, $neat).'</div>';  ?>

	<?php if (isset( $show_date)) {
	if ( $date_style == 'human_time_diff' ) {
		echo '<div class="post_date">'.human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ago</div>';
	} else {
		the_time( $date_style, '<div class="post_date">', '</div>' );
	}
	} ?>
	</li>
	<?php endwhile; ?>
	</ul>
	</div><!-- end widget -->
	<?php endif; wp_reset_postdata();

		if ( $show_all_text_position === 'bottom' ) {
			echo $views_posts_link;
		}
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['num_posts'] = strip_tags( $new_instance['num_posts'] );
		$instance['post_offset'] = strip_tags( $new_instance['post_offset'] );
		$instance['excerpt_length'] = strip_tags( $new_instance['excerpt_length'] );
		$instance['delim'] = $new_instance['delim'];
		$instance['post_type'] = strip_tags( $new_instance['post_type'] );
		$instance['show_date'] = $new_instance['show_date'];
		$instance['date_style'] = strip_tags( $new_instance['date_style'] );
		$instance['neat'] = $new_instance['neat'];

		$instance['category_id'] = esc_attr( $new_instance['category_id']);
		$instance['tag_id'] = esc_attr( $new_instance['tag_id']);
		$instance['read_more'] = $new_instance['read_more'];
		$instance['show_thumbs'] = $new_instance['show_thumbs'];
		$instance['post_thumb_width'] = ( $new_instance['post_thumb_width'] ) ? absint(strip_tags( $new_instance['post_thumb_width'] )) : 60;
		$instance['post_thumb_height'] = ( $new_instance['post_thumb_height'] ) ? absint(strip_tags( $new_instance['post_thumb_height'] )) : 60;
		$instance['show_all_text'] = $new_instance['show_all_text'];
		$instance['show_all_text_position'] = $new_instance['show_all_text_position'];
		$instance['show_all_link'] = strip_tags( $new_instance['show_all_link']);

		return $instance;
	}


	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title' => esc_html__( 'Latest Items', 'kwik' ),
			'tag_id' => '',
			'category_id' => '',
			'num_posts' => 3,
			'post_offset' => 0,
			'post_type' => 'post',
			'excerpt_length' => 60,
			'read_more' => false,
			'show_thumbs' => true,
			'post_thumb_width' => 100,
			'post_thumb_height' => 100,
			'excerpt_length' => 30,
			'show_date' => true,
			'show_all_link' => true,
			'show_all_text' => 'View all',
			'show_all_text_position' => 'top',
			'date_style' => 'human_time_diff',
			'delim' => '&hellip;',
			'neat' => true,
			);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<?php

		$post_type = $instance['post_type'];
		$kc_post_types = get_post_types( array( '_builtin'=> false ) );
		$kc_post_types[] = 'post';

		$fallback = '<select id="'. $this->get_field_id( 'post_type' ).'" name="'. $this->get_field_name( 'post_type' ).'">';

		foreach ( $kc_post_types as $kc_post_type ){
				$cur_post_type = get_post_type_object( $kc_post_type );
				$fallback .= '<option '.( $kc_post_type == $post_type ? 'selected="selected"' : '' ).' value="'.$kc_post_type.'">'.$cur_post_type->labels->name.'</a>';
			}
		$fallback .= '</select>';

		?>

		<script type="text/javascript">
			jQuery(document).ready(function ( $) {
				$( '#<?php echo $this->get_field_id( 'show_date' ); ?>' ).click( function() {
					// console.log("clicked");
					var date_style = $("#<?php echo $this->get_field_id( 'show_date_style' ); ?>-label"), show_date_cb = $(this);
					// console.log(date_style);
					date_style.toggle(250, function(){
						if (show_date_cb.attr( 'checked' ) === undefined) $( 'input[type="checkbox"]', $(this)).removeAttr( 'checked' );
					});
				});
			});
		</script>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'kwik' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>

	<!-- Widget Title: Post Type -->
		<p>
			<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php esc_html_e( 'Post Type:', 'kwik' ); ?></label>
			<?php echo $fallback; ?>
		</p>


		<!-- Show Only Posts with tag -->
	<p>
	  <label for="<?php echo $this->get_field_id( 'tag_id' ); ?>"><?php esc_html_e( 'Show only posts with this tag(s):', 'kwik' ); ?></label>
	  <input id="<?php echo $this->get_field_id( 'tag_id' ); ?>" type="text" name="<?php echo $this->get_field_name( 'tag_id' ); ?>" value="<?php echo $instance['tag_id']; ?>" class="widefat" />
	</p>

	<!-- Show Categories -->
		<p>
			<label for="<?php echo $this->get_field_id( 'category_id' ); ?>"><?php esc_html_e( '.. in a specific category:', 'kwik' ); ?></label>
			<?php wp_dropdown_categories( 'show_option_all=All&hierarchical=1&orderby=name&selected='.$instance['category_id'].'&name='.$this->get_field_name( 'category_id' ).'&class=widefat' ); ?>
		</p>

		<!-- Number of Posts -->
		<p>
			<label for="<?php echo $this->get_field_id( 'num_posts' ); ?>"><?php esc_html_e( 'Number of posts to show:', 'kwik' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_posts' ); ?>" type="text" name="<?php echo $this->get_field_name( 'num_posts' ); ?>" value="<?php echo $instance['num_posts']; ?>" size="2" maxlength="2" />
			<br />
			<small><?php esc_html_e( '(at most 15)', 'kwik' ); ?></small>
		</p>

		<!-- Post Offset -->
		<p>
			<label for="<?php echo $this->get_field_id( 'post_offset' ); ?>"><?php esc_html_e( 'Number of posts to skip:', 'kwik' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'post_offset' ); ?>" type="text" name="<?php echo $this->get_field_name( 'post_offset' ); ?>" value="<?php echo $instance['post_offset']; ?>" size="2" maxlength="2" />
			<br />
			<small><?php esc_html_e( '(offset from newest)', 'kwik' ); ?></small>
		</p>

		<!-- Excerpt Length -->
		<p>
			<label for="<?php echo $this->get_field_id( 'excerpt_length' ); ?>"><?php esc_html_e( 'Excerpt Length:', 'kwik' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'excerpt_length' ); ?>" type="text" name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" value="<?php echo $instance['excerpt_length']; ?>" size="2" maxlength="2" />
		<br/><small><?php esc_html_e( 'Use "0" for no excerpt', 'kwik' ); ?></small>
		</p>

		<!-- Delimiter -->
		<p>
			<label for="<?php echo $this->get_field_id( 'delim' ); ?>"><?php esc_html_e( 'Delimiter:', 'kwik' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'delim' ); ?>" name="<?php echo $this->get_field_name( 'delim' ); ?>" >
				<option value="&hellip;" <?php echo ( $instance['delim'] == '…' ? 'selected="selected"' : '' ); ?>>&hellip;</option>
				<option value="&ndash;" <?php echo ( $instance['delim'] == '–' ? 'selected="selected"' : '' ); ?>>&ndash;</option>
				<option value="&mdash;" <?php echo ( $instance['delim'] == '—' ? 'selected="selected"' : '' ); ?>>&mdash;</option>
				<option value="&raquo;" <?php echo ( $instance['delim'] == '»' ? 'selected="selected"' : '' ); ?>>&raquo;</option>
				<option value="&gt;" <?php echo ( $instance['delim'] == '>' ? 'selected="selected"' : '' ); ?>>&gt;</option>
			</select>
		</p>

	<!-- Show Date -->
		<p>
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>">
				<input class="checkbox" type="checkbox" <?php checked( $instance['show_date'], true ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" class="toggle_field" name="<?php echo $this->get_field_name( 'show_date' ); ?>" value="1" />
				<?php esc_html_e( 'Show Date', 'kwik' ); ?>
			</label><br/>

			<label for="<?php echo $this->get_field_id( 'show_date_style' ); ?>" id="<?php echo $this->get_field_id( 'show_date_style' ); ?>-label" style="display: <?php echo $instance['show_date'] ? "inline-block" : "none" ?>">
				<?php esc_html_e( 'Date Style', 'kwik' ); ?>

			<select id="<?php echo $this->get_field_id( 'date_style' ); ?>" name="<?php echo $this->get_field_name( 'date_style' ); ?>" >
				<option value="human_time_diff" <?php echo ( $instance['date_style'] == "human_time_diff" ? 'selected="selected"' : '' ); ?>><?php echo human_time_diff( current_time( 'timestamp' )-100, current_time( 'timestamp' ) ) . ' ago'; ?></option>
				<option value="Y-m-d" <?php echo ( $instance['date_style'] == 'Y-m-d' ? 'selected="selected"' : '' ); ?>><?php echo date("Y-m-d") ?></option>
				<option value="d-m-Y" <?php echo ( $instance['date_style'] == 'd-m-Y' ? 'selected="selected"' : '' ); ?>><?php echo date("d-m-Y") ?></option>
				<option value="D F dS, Y" <?php echo ( $instance['date_style'] == 'D F dS, Y' ? 'selected="selected"' : '' ); ?>><?php echo date("D F dS, Y") ?></option>
			</select>
			</label>

		</p>


	<!-- Show All Link -->
		<p>
			<label for="<?php echo $this->get_field_id( 'show_all_link' ); ?>">
				<input class="checkbox" type="checkbox" <?php checked( $instance['show_all_link'], true ); ?> id="<?php echo $this->get_field_id( 'show_all_link' ); ?>" name="<?php echo $this->get_field_name( 'show_all_link' ); ?>" value="1" />
				<?php esc_html_e( 'Show All Link', 'kwik' ); ?>
			</label><br/>

			<label for="<?php echo $this->get_field_id( 'show_all_text' ); ?>" id="<?php echo $this->get_field_id( 'show_all_text' ); ?>-label" >
				<?php esc_html_e( 'Link Text', 'kwik' ); ?>
			<input type="text" id="<?php echo $this->get_field_id( 'show_all_text' ); ?>" value="<?php echo $instance['show_all_text'] ?>"  name="<?php echo $this->get_field_name( 'show_all_text' ); ?>" />
			</label><br/>

			<label for="<?php echo $this->get_field_id( 'show_all_text_position' ); ?>" id="<?php echo $this->get_field_id( 'show_all_text_position' ); ?>-label" >
			    <?php esc_html_e( 'Link Text Position', 'kwik' ); ?>
			<select id="<?php echo $this->get_field_id( 'show_all_text_position' ); ?>" name="<?php echo $this->get_field_name( 'show_all_text_position' ); ?>" >
				<option value="top" <?php selected( $instance['show_all_text_position'], 'top' ); ?>>Top</option>
				<option value="bottom" <?php selected( $instance['show_all_text_position'], 'bottom' ); ?>>Bottom</option>
			</select>
            </label>

		</p>


	  <!-- Neat Trim -->
		<p>
			<label for="<?php echo $this->get_field_id( 'neat' ); ?>">
				<input class="checkbox" type="checkbox" <?php checked( $instance['neat'], true ); ?> id="<?php echo $this->get_field_id( 'neat' ); ?>" name="<?php echo $this->get_field_name( 'neat' ); ?>" value="1" <?php checked( '1', $instance['neat']); ?> />
				<?php esc_html_e( 'Neat Trim', 'kwik' ); ?>
			</label><br />
			<small><?php esc_html_e( 'Neat Trim trims on word boundary', 'kwik' ); ?></small>
		</p>


		<!-- Show "Read more ->" link checkbox -->
		<p>
			<label for="<?php echo $this->get_field_id( 'read_more' ); ?>" style="display:none;">
				<input class="checkbox" type="checkbox" <?php checked( $instance['read_more'], true ); ?> id="<?php echo $this->get_field_id( 'read_more' ); ?>" name="<?php echo $this->get_field_name( 'read_more' ); ?>" value="1" <?php checked( '1', $instance['read_more']); ?> />
				<?php esc_html_e( 'Show "View" post button', 'kwik' ); ?>
			</label>
		</p>

		<!-- Show Thumbnails -->
		<p>
			<label for="<?php echo $this->get_field_id( 'show_thumbs' ); ?>">
				<input class="checkbox" type="checkbox" <?php checked( $instance['show_thumbs'], true ); ?> id="<?php echo $this->get_field_id( 'show_thumbs' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbs' ); ?>" value="1" <?php checked( '1', $instance['show_thumbs']); ?> />
				<?php esc_html_e( 'Show thumbnails', 'kwik' ); ?>
			</label>
		</p>

<?php
if ( $instance['show_thumbs']) : ?>
	<!-- Thumb Dimension -->
	<p>
	  <label for="<?php echo $this->get_field_id( 'post_thumb_width' ); ?>"><?php esc_html_e( 'Thumbnail Dimensions:', 'kwik' ); ?></label><br />
	  <input id="<?php echo $this->get_field_id( 'post_thumb_width' ); ?>" type="text" name="<?php echo $this->get_field_name( 'post_thumb_width' ); ?>" value="<?php echo $instance['post_thumb_width']; ?>" size="5" maxlength="4" />
	  <span> &#10005; </span>
	  <input id="<?php echo $this->get_field_id( 'post_thumb_height' ); ?>" type="text" name="<?php echo $this->get_field_name( 'post_thumb_height' ); ?>" value="<?php echo $instance['post_thumb_height']; ?>" size="5" maxlength="4" />
	  <br />
	  <span style="width:58px;display:inline-block;"><?php esc_html_e( 'Width', 'kwik' ); ?></span>&#10005;<span style="margin-left:9px;width:150px;"><?php esc_html_e( 'Height in pixels', 'kwik' ); ?></span>
	</p>

<?php endif; ?>

<?php
	}
}


