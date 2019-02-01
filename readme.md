# Apartment Facility Booking

Make sure PHP and composer are installed

Run `composer install` to configure autoload and get PHPUnit 

## Usage

From the root of the project run these commands to make a booking.

`php book "Club House, 2012-10-26, 16:00,22:00"`
> Booked, Rs. 1000

## Testing

`phpunit --bootstrap vendor/autoload.php tests/BookingControllerTest.php`
