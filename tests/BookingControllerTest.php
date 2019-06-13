<?php

declare(strict_types=1);

use App\Controllers\BookingController;
use PHPUnit\Framework\TestCase;

final class BookingControllerTest extends TestCase
{
    protected $successBooking1;
    protected $successBooking2;

    protected $failureBooking1;
    protected $failureBooking2;
    protected $failureBooking3;

    protected $data_directory;
    protected $booking_date;

    protected function setUp()
    {
        $this->data_directory = 'test_data';
        $this->booking_date = date('Y') . '-12-31';

        if (file_exists($this->data_directory) && is_dir($this->data_directory)) {
            $this->dataCleanUp();
        }
        mkdir($this->data_directory);

        //Generating test bookings
        //Success cases
        $this->successBooking1 = 'Club House, ' . $this->booking_date . ', 16:00,22:00';
        $this->successBooking2 = 'Tennis Court, ' . $this->booking_date . ', 10:00,20:00';

        //Failure cases
        //Same slot failure
        $this->failureBooking1 = 'Club House, ' . $this->booking_date . ', 16:00,22:00';
        //Overlap booking failure
        $this->failureBooking2 = 'Tennis Court, ' . $this->booking_date . ', 11:00,12:00';
        //Past date booking failure *exception*
        try {
            $this->failureBooking3 = 'Tennis Court, ' . (new DateTime($this->booking_date))->modify('-1 Year')->format('Y-m-d') . ', 2012-10-26, 11:00,12:00';
        } catch (Exception $exception) {
            $this->failureBooking3 = (date('Y') - 1) . '-12-31';
        }
    }

    protected function tearDown()
    {
        $this->dataCleanUp();
    }

    protected function dataCleanUp()
    {
        $data_files = glob($this->data_directory . '/*');
        foreach ($data_files as $data_file) {
            unlink($data_file);
        }
        rmdir($this->data_directory);
    }

    public function testSuccessBookings()
    {
        try {
            //Successful bookings
            $booking1 = (new BookingController($this->successBooking1, $this->data_directory))->book();
            $this->assertEquals('Booked, Rs. 3000', $booking1);
            $booking2 = (new BookingController($this->successBooking2, $this->data_directory))->book();
            $this->assertEquals('Booked, Rs. 500', $booking2);
        } catch (BookingException $bookingException) {
            return $bookingException;
        }
    }

    public function testFailureBookings()
    {
        try {
            //Blocking slots
            (new BookingController($this->successBooking1, $this->data_directory))->book();
            (new BookingController($this->successBooking2, $this->data_directory))->book();

            //Testing already booked slots
            $booking3 = (new BookingController($this->failureBooking1, $this->data_directory))->book();
            $this->assertEquals('Booking Failed, Already Booked', $booking3);
            $booking4 = (new BookingController($this->failureBooking2, $this->data_directory))->book();
            $this->assertEquals('Booking Failed, Already Booked', $booking4);
        } catch (BookingException $bookingException) {
            return $bookingException;
        }
    }

    public function testBookingException()
    {
        //Testing exception
        $this->expectException(BookingException::class);
        (new BookingController($this->failureBooking3, $this->data_directory))->book();
    }
}
