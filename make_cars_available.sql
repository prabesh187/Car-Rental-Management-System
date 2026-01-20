-- Make All Cars Available SQL Script
-- This script sets all cars to available status

-- Check current status before update
SELECT 
    'BEFORE UPDATE' as status,
    COUNT(*) as total_cars,
    SUM(CASE WHEN car_availability = 'yes' THEN 1 ELSE 0 END) as available_cars,
    SUM(CASE WHEN car_availability = 'no' THEN 1 ELSE 0 END) as unavailable_cars
FROM cars;

-- Show cars that are currently unavailable
SELECT 
    'CURRENTLY UNAVAILABLE CARS' as info,
    car_id, 
    car_name, 
    car_nameplate, 
    car_availability 
FROM cars 
WHERE car_availability = 'no';

-- Check for currently rented cars (should not be made available if actively rented)
SELECT 
    'CURRENTLY RENTED CARS' as info,
    c.car_id, 
    c.car_name, 
    c.car_nameplate, 
    rc.customer_username, 
    rc.rent_start_date, 
    rc.rent_end_date
FROM cars c 
JOIN rentedcars rc ON c.car_id = rc.car_id 
WHERE rc.return_status = 'NR';

-- Update all cars to available status
-- Note: You may want to exclude currently rented cars
UPDATE cars 
SET car_availability = 'yes' 
WHERE car_id NOT IN (
    SELECT DISTINCT rc.car_id 
    FROM rentedcars rc 
    WHERE rc.return_status = 'NR'
);

-- Alternative: Make ALL cars available (use this if you want to force all cars available)
-- UPDATE cars SET car_availability = 'yes';

-- Check status after update
SELECT 
    'AFTER UPDATE' as status,
    COUNT(*) as total_cars,
    SUM(CASE WHEN car_availability = 'yes' THEN 1 ELSE 0 END) as available_cars,
    SUM(CASE WHEN car_availability = 'no' THEN 1 ELSE 0 END) as unavailable_cars
FROM cars;

-- Show all cars with their final status
SELECT 
    car_id,
    car_name,
    car_nameplate,
    car_availability,
    CASE 
        WHEN car_id IN (
            SELECT rc.car_id 
            FROM rentedcars rc 
            WHERE rc.return_status = 'NR'
        ) THEN 'Currently Rented'
        ELSE 'Available for Booking'
    END as rental_status
FROM cars 
ORDER BY car_id;