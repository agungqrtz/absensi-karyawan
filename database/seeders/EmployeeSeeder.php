<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// database/seeders/EmployeeSeeder.php
use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            'Joko', 'Rudi', 'Ruli', 'Wandut', 'Saeri', 'Larso', 'Dewo', 'Catur',
            'Basong', 'Wawan la', 'Solikin', 'Bowo', 'Iki', 'Isbat', 'Gafur', 
            'Hendra', 'Marlin', 'Kasim'
        ];

        foreach ($employees as $name) {
            Employee::create(['name' => $name]);
        }
    }
}
