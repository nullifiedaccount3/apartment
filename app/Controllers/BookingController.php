<?php

namespace App\Controllers;


class BookingController
{

    private $booking_request;
    private $data_directory;

    private $config;

    /**
     * BookingController constructor.
     * @param $booking_request
     * @param string $data_directory
     */
    function __construct($booking_request, $data_directory)
    {
        if ($data_directory == null) {
            $this->data_directory = 'data';
        } else {
            $this->data_directory = $data_directory;
        }

        if (!file_exists($this->data_directory)) {
            mkdir($this->data_directory);
        }

        $this->booking_request = array_map('trim', explode(',', $booking_request));;
        $this->config = config();
    }

    /**
     * @return bool|string
     * @throws \BookingException
     */
    function book()
    {
        $is_valid = $this->validate();
        if ($is_valid) {
            //Get facility, date and pricing
            //Booking confirmation string = facility+date+from+to+cost
            $facility = $this->booking_request[0];
            $date = $this->booking_request[1];
            $from = intval($this->booking_request[2]);
            $to = intval($this->booking_request[3]);

            //Check slot price
            $cost = 0;
            $no_slot = true;
            $slot_costs = $this->config['Facilities'][$facility]['cost']['hourly'];
            foreach ($slot_costs as $slot_cost) {
                if ($from >= $slot_cost['from'] && $to <= $slot_cost['to']) {
                    $no_slot = false;
                    $cost = ($to - $from) * $slot_cost['price'];
                    break;
                } else {
                    continue;
                }
            }
            if ($no_slot) {
                throw new \BookingException('No slots are available');
            }
            $booking_confirmation = $facility . '*' . $from . '*' . $to . '*' . $cost;
            if (file_exists($this->data_directory . '/' . $date)) {
                $all_bookings = explode(PHP_EOL, file_get_contents($this->data_directory . '/' . $date));

                foreach ($all_bookings as $booking) {
                    $booking = array_map('trim', explode('*', $booking));
                    if (count($booking) !== 4) {
                        continue;
                    }
                    //overlaps if x.End > y.Start AND y.End > x.Start
                    if ($to > (int)$booking[1] && (int)$booking[2] > $from && $facility == $booking[0]) {
                        return 'Booking Failed, Already Booked';
                    }
                }
            }
            file_put_contents($this->data_directory . '/' . $date, $booking_confirmation . PHP_EOL, FILE_APPEND);
            return 'Booked, Rs. ' . $cost;
        } else {
            return $is_valid;
        }
    }

    /**
     * @return bool
     * @throws \BookingException
     */
    private function validate()
    {
        $facility = $this->booking_request[0];
        $date = $this->booking_request[1];
        $from = str_replace(':', '.', $this->booking_request[2]);
        $to = str_replace(':', '.', $this->booking_request[3]);

        if (!array_key_exists($facility, $this->config['Facilities'])) {
            throw new \BookingException('Please choose from available facilities. Available facilities are: ' .
                implode(', ', array_keys($this->config['Facilities'])));
        };

        $date_string_array = explode('-', $date);

        if ((count($date_string_array) !== 3) ||
            !checkdate($date_string_array[1], $date_string_array[2], $date_string_array[0])) {
            throw new \BookingException('Date must valid and use the format 2012-10-26');
        }

        $from = is_numeric($from) ? intval($from) : $from;
        $to = is_numeric($to) ? intval($to) : $to;
        if (!is_int($from) || !is_int($to) || ($from > $to)) {
            throw new \BookingException('From and To times must be valid and From must be smaller than To');
        }

        try {
            if ((new \DateTime($date)) < (new \DateTime())) {
                throw new \BookingException('Booking date cannot be in the past');
            }
        } catch (\Exception $exception) {
            throw new \BookingException('Booking date cannot be in the past');
        }

        return true;
    }

}
