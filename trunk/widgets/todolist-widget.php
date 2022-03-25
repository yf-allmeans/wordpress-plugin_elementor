<?php

/**
 * Elementor_Todolist class.
 *
 * @category   Class
 * @package    ElementorTodolist
 * @subpackage WordPress
 * @author     Gabriel Redondo
 * @copyright  2022 Gabriel Redondo
 * @license    https://www.gnu.org/licenses/gpl-2.0.html
 * @since      1.0.0
 * php version 7.3.9
 */

 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
 
class Todolist_Widget extends \Elementor\Widget_Base {
    //todo list widget name
    public function get_name() {
        return 'todolist-task';
    }
    //todo list widget title on elementor
    public function get_title() {
        return __( 'Todo List', 'todolist-elementor-add-on' );
    }
    //todo list widget icon on elementor
    public function get_icon() {
        return 'eicon-post';
    }
    //category
    public function get_categories() {
        return [ 'general' ];
    }
    //importing bootstrap
    public function get_script_depends() {
        wp_register_script("bootstrap-js", "https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js", array(), false, true);
         
        return [
            'bootstrap-js'
        ];
    }
    //importing css
    public function get_style_depends() {
        wp_register_style( "bootstrap-css", "https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css", array(), false, "all" );
        return [
            'bootstrap-css'
        ];
    }
    //widget controls
    protected function register_controls() {
        //main content label
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Content', 'todolist-elementor-add-on' ),
            ]
        );
        //title1 textbox for ongoing tasks --user input preference changeable
        $this->add_control(
            'title', [
                'label' => esc_html__( 'Title', 'todolist-elementor-add-on' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Ongoing Tasks' , 'todolist-elementor-add-on' ),
            ]
        );
        //title2 textbox for finished tasks --user input preference changeable
        $this->add_control(
            'title2', [
                'label' => esc_html__( 'Title2', 'todolist-elementor-add-on' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Finished Tasks' , 'todolist-elementor-add-on' ),
            ]
        );
        //filter tasks to display
        //default --ongoing tasks & finished tasks
        //ongoing tasks
        //finished tasks
        $this->add_control(
			'filter',
			[
				'label' => esc_html__( 'Filter', 'todolist-elementor-add-on' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default'  => esc_html__( 'Default', 'todolist-elementor-add-on' ),
					'ongoing' => esc_html__( 'Ongoing Tasks', 'todolist-elementor-add-on' ),
					'done' => esc_html__( 'Finished Tasks', 'todolist-elementor-add-on' ),
				],
			]
		);
  
        $this->end_controls_section();
  
    }
      
    protected function render() {
        // generate the final HTML on the frontend using PHP
        $settings = $this->get_settings_for_display();
        global $wpdb;
        $table_name = $wpdb->prefix . 'todo';
        //display for default filter settings
        if($settings['filter']=='default'){
        ?>      <h1><?php echo wp_kses( $settings['title'], array() ); ?></h1>
                <?php 
                $this->add_inline_editing_attributes( 'title', 'none' );
                $this->add_inline_editing_attributes( 'title2', 'none' );
                //database query for ongoing tasks
                $result = $wpdb->get_results("SELECT * FROM $table_name where status<>'Done'"); 
                ?>
                <table class="wp-list-table striped">
                <!-- table header for ongoing tasks -->
                <thead>
                    <tr>
                        <th width="25%">Task ID</th>
                        <th width="25%">Task</th>
                        <th width="25%">Status</th>
                        <th width="25%">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                //loop called data to display in rows
                foreach ($result as $print) {
                            echo "
                            <tr>
                                <td width='25%'>$print->id</td>
                                <td width='25%'>$print->todo</td>
                                <td width='25%'>$print->status</td>
                                <td width='25%'>$print->date</td>
                            </tr>
                            ";
                        }
                    ?>
                </table>
                <?php
                //database query for finished tasks
                $result2 = $wpdb->get_results("SELECT * FROM $table_name where status='Done'"); 
                //validation if ever query did not return any data
                if ($wpdb->num_rows>0){
                ?>
                    <h1><?php echo wp_kses( $settings['title2'], array() ); ?></h1>
                <?php
                    echo "
                    <table class='wp-list-table striped'>
                    <thead>
                        <tr>
                            <th width='25%'>Task ID</th>
                            <th width='25%''>Task</th>
                            <th width='25%'>Status</th>
                            <th width='25%'>Date</th>
                        </tr>
                    </thead>
                    <tbody>";
                    //loop data to display into rows
                    foreach ($result2 as $print2) {
                                echo "
                                <tr>
                                    <td width='25%'>$print2->id</td>
                                    <td width='25%'>$print2->todo</td>
                                    <td width='25%'>$print2->status</td>
                                    <td width='25%'>$print2->date</td>
                                </tr>
                                ";
                            }
                        ?>
                    </table>
            <?php 
                }
            }
            //display for ongoing filter settings
            elseif($settings['filter']=='ongoing'){
                $this->add_inline_editing_attributes( 'title', 'none' );
                ?>
                <h1><?php echo wp_kses( $settings['title'], array() ); ?></h1>
                <?php 
                //database query for ongoing tasks
                $result = $wpdb->get_results("SELECT * FROM $table_name where status<>'Done'"); 
                ?>
                <table class="wp-list-table striped">
                <!-- ongoing tasks table header -->
                <thead>
                    <tr>
                        <th width="25%">Task ID</th>
                        <th width="25%">Task</th>
                        <th width="25%">Status</th>
                        <th width="25%">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                // loop called data to display into rows
                foreach ($result as $print) {
                            echo "
                            <tr>
                                <td width='25%'>$print->id</td>
                                <td width='25%'>$print->todo</td>
                                <td width='25%'>$print->status</td>
                                <td width='25%'>$print->date</td>
                            </tr>
                            ";
                        }
                    ?>
                </table>
            <?php
            }
            //display for finished tasks filter settings
            elseif($settings['filter']=='done'){
                $this->add_inline_editing_attributes( 'title2', 'none' );
                //database query for finished tasks
                $result2 = $wpdb->get_results("SELECT * FROM $table_name where status='Done'"); 
                ?>
                    <h1><?php echo wp_kses( $settings['title2'], array() ); ?></h1>
                <?php
                // table header for finished tasks
                    echo "
                    <table class='wp-list-table striped'>
                    <thead>
                        <tr>
                            <th width='25%'>Task ID</th>
                            <th width='25%''>Task</th>
                            <th width='25%'>Status</th>
                            <th width='25%'>Date</th>
                        </tr>
                    </thead>
                    <tbody>";
                    // loop called data in order to display into rows
                    foreach ($result2 as $print2) {
                                echo "
                                <tr>
                                    <td width='25%'>$print2->id</td>
                                    <td width='25%'>$print2->todo</td>
                                    <td width='25%'>$print2->status</td>
                                    <td width='25%'>$print2->date</td>
                                </tr>
                                ";
                            }
                        ?>
                    </table>
            <?php 
        }
    
    }
    protected function _content_template() {
        //default filter setting render inline editing attributes
		if($settings['filter']=='default'){
?>
 		<#
		view.addInlineEditingAttributes( 'title', 'none' );
        view.addInlineEditingAttributes( 'title2', 'none' );
		#>
        <h2 {{{ view.getRenderAttributeString( 'title' ) }}}>{{{ settings.title }}}</h2>
        <h2 {{{ view.getRenderAttributeString( 'title2' ) }}}>{{{ settings.title2 }}}</h2>
<?php
        }
        //finished task filter setting render inline editing attributes
        elseif($settings['filter']=='done'){
?>
        <#
        view.addInlineEditingAttributes( 'title2', 'none' );
		#>
        <h2 {{{ view.getRenderAttributeString( 'title2' ) }}}>{{{ settings.title2 }}}</h2>
<?php
        }
        //ongoing task filter setting render inline editing attributes
        elseif($settings['filter']=='ongoing'){
?>
        <#
        view.addInlineEditingAttributes( 'title', 'none' );
		#>
        <h2 {{{ view.getRenderAttributeString( 'title' ) }}}>{{{ settings.title }}}</h2>
<?php
        }

}
}