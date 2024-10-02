<?php
require_once "../Model/Event.php";

class EventController {
    private $eventModel;

    public function __construct() {
        $this->eventModel = new Event();
    }

    public function listAvailableEvents() {
        $events = $this->eventModel->getAllEvents();
        require "../views/events/list.php";
    }

    public function showEventDetails($id) {
        $event = $this->eventModel->getEventById($id);
        require "../views/events/details.php";
    }

    public function registerForEvent($userId, $eventId) {
        $this->eventModel->registerUserForEvent($userId, $eventId);
        // Redirect to registered events page
    }

    public function cancelRegistration($userId, $eventId) {
        $this->eventModel->cancelRegistration($userId, $eventId);
        // Redirect to registered events page
    }

    public function listRegisteredEvents($userId) {
        $events = $this->eventModel->getRegisteredEvents($userId);
        require "../views/events/registered.php";
    }
}