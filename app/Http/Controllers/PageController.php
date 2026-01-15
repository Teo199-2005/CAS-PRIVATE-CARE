<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * PageController
 * 
 * Handles simple static page views that don't require
 * any business logic or data processing.
 */
class PageController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function register()
    {
        return view('register');
    }

    public function faq()
    {
        return view('faq');
    }

    public function about()
    {
        return view('about');
    }

    public function caregiverNewYork()
    {
        return view('caregiver-new-york');
    }

    public function housekeeperNewYork()
    {
        return view('housekeeper-new-york');
    }

    public function hireCaregiverNewYork()
    {
        return view('hire-caregiver-new-york');
    }

    public function caregiverBrooklyn()
    {
        return view('caregiver-brooklyn');
    }

    public function caregiverManhattan()
    {
        return view('caregiver-manhattan');
    }

    public function caregiverQueens()
    {
        return view('caregiver-queens');
    }

    public function caregiverBronx()
    {
        return view('caregiver-bronx');
    }

    public function caregiverStatenIsland()
    {
        return view('caregiver-staten-island');
    }

    public function contractorPartner()
    {
        return view('contractor-partner');
    }

    public function housekeepingPersonalAssistant()
    {
        return view('housekeeping-personal-assistant');
    }

    public function housekeepingNewYork()
    {
        return view('housekeeping-new-york');
    }

    public function personalAssistantNewYork()
    {
        return view('personal-assistant-new-york');
    }

    public function trainingCenter()
    {
        return view('training-center');
    }

    public function apiTest()
    {
        return view('api-test');
    }

    public function services()
    {
        return view('services');
    }

    public function contractors()
    {
        return view('contractors');
    }
}
