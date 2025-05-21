<?php
namespace App\Controllers\Admin;

use App\Services\Analytics\BookingAnalytics;
use App\Services\Analytics\RevenueAnalytics;
use App\Services\Analytics\OccupancyAnalytics;

class DashboardController extends AdminBaseController {
    public function index() {
        $period = $_GET['period'] ?? 'week';
        
        $bookingAnalytics = new BookingAnalytics();
        $revenueAnalytics = new RevenueAnalytics();
        $occupancyAnalytics = new OccupancyAnalytics();
        
        $data = [
            'bookings' => $bookingAnalytics->getStatistics($period),
            'revenue' => $revenueAnalytics->getStatistics($period),
            'occupancy' => $occupancyAnalytics->getStatistics($period)
        ];
        
        $this->render('admin/dashboard', [
            'title' => 'Tableau de bord',
            'data' => $data,
            'period' => $period
        ]);
    }
}

