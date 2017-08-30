<?php
/**
 * HashBuddy_Tag_Cloud
 */
class HashBuddy_Tag_Cloud extends WP_Widget {

	public $id = 'hashbuddy_tag_cloud';
	public $name = 'Hashtag Cloud';
	public $widget_ops = array();

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$this->widget_ops = array(
			'classname' => 'hashbuddy_tag_cloud',
			'description' => 'Most Popular Hashtags',
		);
		parent::__construct( $this->id, $this->name, $this->widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		?>
		<style>

			.hashtag-cloud a:link, #tagcloud a:visited {
				text-decoration:none;
				color: #333;
			}

			.hashtag-cloud a:hover {
				text-decoration: underline;
			}

			.hashtag-cloud span {
				padding: 4px;
			}

			.hashtag-cloud .smallest {
				font-size: 12px;
			}

			.hashtag-cloud .small {
				font-size: 15px;
			}

			.hashtag-cloud .medium {
				font-size: 17px;
			}

			.hashtag-cloud .large {
				font-size: 19px;
			}

			.hashtag-cloud .largest {
				font-size: 21px;
			}
		</style>
		<?php
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$amount = $instance['amount'] ? (int) $instance['amount'] : 20;
		$this->get( $amount );

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Hashtag Cloud', 'hashbuddy' );
		$amount = ! empty( $instance['amount'] ) ? $instance['amount'] : '20';
		?>
		<p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'hashbuddy' ); ?></label>
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'amount' ) ); ?>"><?php esc_attr_e( 'Amount:', 'hashbuddy' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'amount' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'amount' ) ); ?>">
				<option value="10" <?php if ( '10' === $amount ) { echo 'selected'; } ?>>10</option>
				<option value="15" <?php if ( '15' === $amount ) { echo 'selected'; } ?>>15</option>
				<option value="20" <?php if ( '20' === $amount ) { echo 'selected'; } ?>>20</option>
				<option value="25" <?php if ( '25' === $amount ) { echo 'selected'; } ?>>25</option>
				<option value="30" <?php if ( '30' === $amount ) { echo 'selected'; } ?>>30</option>
				<option value="35" <?php if ( '35' === $amount ) { echo 'selected'; } ?>>35</option>
				<option value="40" <?php if ( '40' === $amount ) { echo 'selected'; } ?>>40</option>
			</select>
		</p>

		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['amount'] = ( ! empty( $new_instance['amount'] ) ) ? strip_tags( $new_instance['amount'] ) : '20';

		return $instance;
	}

	/**
	 * Get hashtag diplay
	 *
	 * @param  integer $amount
	 * @return void
	 */
	public function get( $amount = 20 ) {

		$terms = hashbuddy_get_hashtags();
		$maximum = 200;

		$terms = array_slice( $terms['terms'], 0, $amount );
		$activity_url = trailingslashit( get_bloginfo( 'url' ) ) . BP_ACTIVITY_SLUG;

		echo '<div class="hashtag-cloud">';

		foreach ( $terms as $term => $value ) {

			$percent = floor( ( (int) $value / $maximum ) * 100 );

			if ( $percent < 20 ) :
				$class = 'smallest';
			elseif ( $percent >= 20 and $percent < 40 ) :
				$class = 'small';
			elseif ( $percent >= 40 and $percent < 60 ) :
				$class = 'medium';
			elseif ( $percent >= 60 and $percent < 80 ) :
				$class = 'large';
			else :
				$class = 'largest';
			endif;

			?>

			<span class="<?php echo esc_attr( $class ); ?>">
				<?php echo '<a href="' . esc_url_raw( $activity_url ) . '/?s=%23' . esc_attr( $term ) . '" rel="nofollow" class="hashtag ' . esc_attr( $term ) . '">#' . esc_attr( $term ) . '</a>'; ?>
			</span>
			<?php
		}

		echo '</div>';
	}
}

class HashBuddy_Tag_Count extends WP_Widget {

	public $id = 'hashbuddy_tag_count';

	public $name = 'Count';

	public $widget_ops = array();

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'hashbuddy_tag_count',
			'description' => 'List of Hashtag Counts',
		);
		parent::__construct( $this->id, $this->name, $this->widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		?>
		<style>
			.hashtag-count ul {
				list-style: none;
			}

			.hashtag-count ul li {
				padding-bottom: 10px;
			}

			.hashtag-count .tag-count {
				background: #eee;
				border-radius: 25%;
				border: 1px solid #ccc;
				color: #6c6c6c;
				display: inline;
				font-size: 70%;
				margin-left: 2px;
				padding: 3px 6px;
				text-align: center;
				vertical-align: middle;
			}

		</style>
		<?php
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$amount = $instance['amount'] ? (int) $instance['amount'] : 20;
		$this->get( $amount );

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Hashtag Count', 'hashbuddy' );
		$amount = ! empty( $instance['amount'] ) ? $instance['amount'] : '20';
		?>
		<p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'hashbuddy' ); ?></label>
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'amount' ) ); ?>"><?php esc_attr_e( 'Amount:', 'hashbuddy' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'amount' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'amount' ) ); ?>">
				<option value="10" <?php if ( '10' === $amount ) { echo 'selected'; } ?>>10</option>
				<option value="15" <?php if ( '15' === $amount ) { echo 'selected'; } ?>>15</option>
				<option value="20" <?php if ( '20' === $amount ) { echo 'selected'; } ?>>20</option>
				<option value="25" <?php if ( '25' === $amount ) { echo 'selected'; } ?>>25</option>
				<option value="30" <?php if ( '30' === $amount ) { echo 'selected'; } ?>>30</option>
				<option value="35" <?php if ( '35' === $amount ) { echo 'selected'; } ?>>35</option>
				<option value="40" <?php if ( '40' === $amount ) { echo 'selected'; } ?>>40</option>
			</select>
		</p>

		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['amount'] = ( ! empty( $new_instance['amount'] ) ) ? strip_tags( $new_instance['amount'] ) : '20';

		return $instance;
	}

	/**
	 * Get hashtag diplay
	 *
	 * @param  integer $amount
	 * @return void
	 */
	public function get( $amount = 20 ) {
		$terms = hashbuddy_get_hashtags();

		$terms = array_slice( $terms['terms'], 0, $amount );
		$activity_url = trailingslashit( get_bloginfo( 'url' ) ) . BP_ACTIVITY_SLUG;

		echo '<div class="hashtag-count"><ul>';

		foreach ( $terms as $term => $value ) {
			?>
				<li><?php echo '<span class="tag-count">' . $value . '</span>  <a href="' . esc_url_raw( $activity_url ) . '/?s=%23' . esc_attr( $term ) . '" rel="nofollow" class="hashtag ' . esc_attr( $term ) . '">#' . esc_attr( $term ) . '</a>'; ?></li>
			<?php
		}

		echo '</ul></div>';
	}
}

/**
 * Register hashtag widgets
 *
 * @return void
 */
function register_hashbuddy_widgets() {
	register_widget( 'HashBuddy_Tag_Cloud' );
	register_widget( 'HashBuddy_Tag_Count' );
}
add_action( 'widgets_init', 'register_hashbuddy_widgets' );

/**
 * Hashtag cloud template tag
 *
 * @return void
 */
function hashtag_cloud() {
	$cloud = new HashBuddy_Tag_Cloud();
	$cloud->get();
}

/**
 * Hashtag count template tag
 *
 * @return void
 */
function hashtag_count() {
	$count = new HashBuddy_Tag_Count();
	$count->get();
}

/**
 * Hashtag shortcode
 *
 * @param  array $atts
 * @return void
 */
function hashtag_shortcode( $atts ) {

	// [hashtag type="cloud"]
	$a = shortcode_atts( array(
		'type' => 'cloud',
	), $atts );

	switch ( $a['type'] ) {
		case 'cloud':
			hashtag_cloud();
		break;
		case 'count':
			hashtag_count();
		break;
	}
}
add_shortcode( 'hashtag', 'hashtag_shortcode' );
