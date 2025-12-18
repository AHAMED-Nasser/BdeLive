<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Admin;

use App\Modules\Controllers\AdminController;
use App\Modules\Repositories\EventRegistrationRepository;
use App\Modules\Repositories\EventRepository;

class EventAttendeesController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $eventId = (int) ($_GET['id'] ?? 0);
        if ($eventId <= 0) {
            header('Location: index.php?page=home');
            exit;
        }

        $registrationRepo = new EventRegistrationRepository();
        $eventRepo = new EventRepository();

        $event = $eventRepo->getEventById($eventId);
        $attendees = $registrationRepo->getRegistrationsByEventId($eventId);

        $this->render('admin/eventAttendeesView', [
            'event' => $event,
            'attendees' => $attendees
        ]);
    }
}