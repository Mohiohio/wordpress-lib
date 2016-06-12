<?php
namespace Mohiohio\WordPress;

use Mohiohio\WordPress\Setting;
use Mohiohio\WordPress;

abstract class Admin
{
    protected $section_id = 'settings';
    protected $section_title = 'Settings';
    protected $page;
    protected $fields = [];
    protected $capability = 'manage_options';

    abstract function get_page_name();

    abstract function init();

    static function get_settings_namespace() {
        throw new \Exception('must implement this');
    }


    /**
     * Just pass name which is an assoc array -
     * what you see here are legacy params
     */
    function add_field($name, $title=null, \Closure $display_callback=null, Setting\Section $section=null, $default=null)
    {
        if(is_array($name)){

            $field = $name + [
                $display_callback=null,
                $section = null,
                $default = null,
                $type = 'text'
            ];

        } else {
            //legacy full param format
            $field = compact('name','title','display_callback','section');
        }

        if(empty($field['section'])){
            $field['section'] = $this->get_default_section();
        }


        $this->fields[$field['name']] = $field;
    }

    function create_section($title,$id=null)
    {
        if(!$id) { $id = $title; }

        return new Setting\Section($id, $title, null, $this->get_settings_namespace());
    }

    function render() {

        $field_objects = array_map(function($data){
            return new Setting\Field($data);
        }, $this->fields);

        new Setting\Page($this->get_settings_namespace(), $this->get_page_name(), $field_objects, null, $this->capability);
    }

    function get_section(Setting\Section $section=null){

        if(!$section){
            $section = $this->get_default_section();
        }

        return $section;
    }

    function get_default_section(){

        static $section;

        if(!$section){

            $id = $this->section_id ?: $this->section_title;

            $section = new Setting\Section($this->section_id, $this->section_title, null, $this->get_settings_namespace());
        }

        return $section;
    }

    static function get_setting($name,$default=null){
        return WordPress::get_option(static::get_settings_namespace(),$name, $default);
    }

}
