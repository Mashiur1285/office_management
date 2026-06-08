<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class AirportSeeder extends Seeder
{
    public function run(): void
    {
        $airports = [
            // Bangladesh
            ['code' => 'DAC', 'name' => 'Hazrat Shahjalal International Airport', 'country' => 'Bangladesh'],
            ['code' => 'CGP', 'name' => 'Shah Amanat International Airport', 'country' => 'Bangladesh'],
            ['code' => 'ZYL', 'name' => 'Osmani International Airport', 'country' => 'Bangladesh'],
            ['code' => 'JSR', 'name' => 'Jessore Airport', 'country' => 'Bangladesh'],
            ['code' => 'CXB', 'name' => "Cox's Bazar Airport", 'country' => 'Bangladesh'],
            ['code' => 'BZL', 'name' => 'Barisal Airport', 'country' => 'Bangladesh'],
            ['code' => 'RJH', 'name' => 'Shah Makhdum Airport', 'country' => 'Bangladesh'],

            // Middle East
            ['code' => 'DXB', 'name' => 'Dubai International Airport', 'country' => 'UAE'],
            ['code' => 'AUH', 'name' => 'Abu Dhabi International Airport', 'country' => 'UAE'],
            ['code' => 'SHJ', 'name' => 'Sharjah International Airport', 'country' => 'UAE'],
            ['code' => 'DOH', 'name' => 'Hamad International Airport', 'country' => 'Qatar'],
            ['code' => 'KWI', 'name' => 'Kuwait International Airport', 'country' => 'Kuwait'],
            ['code' => 'BAH', 'name' => 'Bahrain International Airport', 'country' => 'Bahrain'],
            ['code' => 'MCT', 'name' => 'Muscat International Airport', 'country' => 'Oman'],
            ['code' => 'RUH', 'name' => 'King Khalid International Airport', 'country' => 'Saudi Arabia'],
            ['code' => 'JED', 'name' => 'King Abdulaziz International Airport', 'country' => 'Saudi Arabia'],
            ['code' => 'MED', 'name' => 'Prince Mohammad Bin Abdulaziz Airport', 'country' => 'Saudi Arabia'],
            ['code' => 'DMM', 'name' => 'King Fahd International Airport', 'country' => 'Saudi Arabia'],
            ['code' => 'TLV', 'name' => 'Ben Gurion International Airport', 'country' => 'Israel'],
            ['code' => 'AMM', 'name' => 'Queen Alia International Airport', 'country' => 'Jordan'],
            ['code' => 'BEY', 'name' => 'Rafic Hariri International Airport', 'country' => 'Lebanon'],

            // South Asia
            ['code' => 'DEL', 'name' => 'Indira Gandhi International Airport', 'country' => 'India'],
            ['code' => 'BOM', 'name' => 'Chhatrapati Shivaji Maharaj International Airport', 'country' => 'India'],
            ['code' => 'CCU', 'name' => 'Netaji Subhash Chandra Bose International Airport', 'country' => 'India'],
            ['code' => 'MAA', 'name' => 'Chennai International Airport', 'country' => 'India'],
            ['code' => 'BLR', 'name' => 'Kempegowda International Airport', 'country' => 'India'],
            ['code' => 'HYD', 'name' => 'Rajiv Gandhi International Airport', 'country' => 'India'],
            ['code' => 'AMD', 'name' => 'Sardar Vallabhbhai Patel International Airport', 'country' => 'India'],
            ['code' => 'CMB', 'name' => 'Bandaranaike International Airport', 'country' => 'Sri Lanka'],
            ['code' => 'KTM', 'name' => 'Tribhuvan International Airport', 'country' => 'Nepal'],
            ['code' => 'ISB', 'name' => 'New Islamabad International Airport', 'country' => 'Pakistan'],
            ['code' => 'KHI', 'name' => 'Jinnah International Airport', 'country' => 'Pakistan'],
            ['code' => 'LHE', 'name' => 'Allama Iqbal International Airport', 'country' => 'Pakistan'],

            // Southeast Asia
            ['code' => 'KUL', 'name' => 'Kuala Lumpur International Airport', 'country' => 'Malaysia'],
            ['code' => 'SIN', 'name' => 'Singapore Changi Airport', 'country' => 'Singapore'],
            ['code' => 'BKK', 'name' => 'Suvarnabhumi Airport', 'country' => 'Thailand'],
            ['code' => 'DMK', 'name' => 'Don Mueang International Airport', 'country' => 'Thailand'],
            ['code' => 'CGK', 'name' => 'Soekarno-Hatta International Airport', 'country' => 'Indonesia'],
            ['code' => 'MNL', 'name' => 'Ninoy Aquino International Airport', 'country' => 'Philippines'],
            ['code' => 'SGN', 'name' => 'Tan Son Nhat International Airport', 'country' => 'Vietnam'],
            ['code' => 'HAN', 'name' => 'Noi Bai International Airport', 'country' => 'Vietnam'],
            ['code' => 'RGN', 'name' => 'Yangon International Airport', 'country' => 'Myanmar'],

            // East Asia
            ['code' => 'HKG', 'name' => 'Hong Kong International Airport', 'country' => 'Hong Kong'],
            ['code' => 'PEK', 'name' => 'Beijing Capital International Airport', 'country' => 'China'],
            ['code' => 'PVG', 'name' => 'Shanghai Pudong International Airport', 'country' => 'China'],
            ['code' => 'CAN', 'name' => 'Guangzhou Baiyun International Airport', 'country' => 'China'],
            ['code' => 'KMG', 'name' => 'Kunming Changshui International Airport', 'country' => 'China'],
            ['code' => 'NRT', 'name' => 'Narita International Airport', 'country' => 'Japan'],
            ['code' => 'ICN', 'name' => 'Incheon International Airport', 'country' => 'South Korea'],

            // Europe
            ['code' => 'LHR', 'name' => 'Heathrow Airport', 'country' => 'United Kingdom'],
            ['code' => 'LGW', 'name' => 'Gatwick Airport', 'country' => 'United Kingdom'],
            ['code' => 'CDG', 'name' => 'Charles de Gaulle Airport', 'country' => 'France'],
            ['code' => 'FRA', 'name' => 'Frankfurt Airport', 'country' => 'Germany'],
            ['code' => 'AMS', 'name' => 'Amsterdam Airport Schiphol', 'country' => 'Netherlands'],
            ['code' => 'IST', 'name' => 'Istanbul Airport', 'country' => 'Turkey'],
            ['code' => 'FCO', 'name' => 'Leonardo da Vinci International Airport', 'country' => 'Italy'],
            ['code' => 'MAD', 'name' => 'Adolfo Suárez Madrid–Barajas Airport', 'country' => 'Spain'],

            // North America
            ['code' => 'JFK', 'name' => 'John F. Kennedy International Airport', 'country' => 'USA'],
            ['code' => 'LAX', 'name' => 'Los Angeles International Airport', 'country' => 'USA'],
            ['code' => 'ORD', 'name' => "O'Hare International Airport", 'country' => 'USA'],
            ['code' => 'YYZ', 'name' => 'Toronto Pearson International Airport', 'country' => 'Canada'],

            // Australia
            ['code' => 'SYD', 'name' => 'Sydney Airport', 'country' => 'Australia'],
            ['code' => 'MEL', 'name' => 'Melbourne Airport', 'country' => 'Australia'],
        ];

        foreach ($airports as $airport) {
            Airport::updateOrCreate(
                ['code' => $airport['code']],
                ['name' => $airport['name'], 'country' => $airport['country']]
            );
        }
    }
}
