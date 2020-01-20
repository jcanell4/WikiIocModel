<?php

/**
 * Description of MoodleProjectModel
 *
 * @author josep
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "datamodel/AbstractProjectModel.php");


abstract class MoodleProjectModel extends AbstractProjectModel{
    
    function sendCalendarDates($token){
        $ws = new WsMoodleCalendar();
        $ws->setToken($token);
        
        $courseId = $this->getCourseId();
        $oldEvents = EventMoodle::getListFromJson($ws->getEvents(array($courseId)));
        //control d'errors
        
        $events  = [];
        foreach ($oldEvents->events as $item){
            if($item->getEventType()=="course" && $item->getCourseId()==$courseId){
                $events[] =$item;
            }
        }
        
        if(count($events)>0){
            $resp = $ws->deleteEventsFromEvents($events);
            //control d'errors
        }
        
        $dates = $this->getCalendarDates();
        $events = [];
        foreach ($dates as $item) {
            $events[] = [
                "name" => $item["title"],
                "timestart" => strptime($item["date"], "%Y-%m-%d"), 
                "courseid" => $courseId,
                "eventtype" => "course",
                "description" => $item['description']
            ];
        }
        
        $resp = $ws->createEvents($events);
        //control d'errors
        
    }
    
    public abstract function getCourseId();

    /**
     * Llista de les dates a pujar al calendari amb el format següent:
     *  - title
     *  - date (en format yyyy-mm-dd)
     *  - description
     */
    public abstract function getCalendarDates();    
}
