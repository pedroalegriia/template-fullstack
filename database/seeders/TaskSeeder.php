<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = [
            [
                'title' => 'Revisar documentación del proyecto',
                'description' => 'Leer el README y documentación técnica antes de desarrollar.',
                'status' => 'completada',
            ],
            [
                'title' => 'Configurar entorno de desarrollo',
                'description' => 'Instalar dependencias, configurar .env y base de datos MySQL.',
                'status' => 'completada',
            ],
            [
                'title' => 'Implementar API REST de tareas',
                'description' => 'Crear endpoints CRUD con validaciones y respuestas JSON.',
                'status' => 'en_progreso',
            ],
            [
                'title' => 'Crear interfaz de gestión de tareas',
                'description' => 'Frontend con HTML, SASS y JavaScript consumiendo la API.',
                'status' => 'pendiente',
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
