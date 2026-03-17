<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Laravel Fundamentals Course
        $laravelCourse = Course::where('title', 'Laravel Fundamentals')->first();
        $laravelModules = [
            ['title' => 'Installation & Project Setup', 'content' => 'Panduan instalasi Laravel dan setup project baru', 'order' => 1, 'is_locked' => false, 'prerequisite_module_id' => null],
            ['title' => 'Routing Basics', 'content' => 'Memahami routing system di Laravel', 'order' => 2, 'is_locked' => false, 'prerequisite_module_id' => null],
            ['title' => 'Controllers & Middleware', 'content' => 'Membuat dan menggunakan controllers dan middleware', 'order' => 3, 'is_locked' => true, 'prerequisite_module_id' => null],
            ['title' => 'Database & Eloquent ORM', 'content' => 'Working dengan database menggunakan Eloquent', 'order' => 4, 'is_locked' => true, 'prerequisite_module_id' => null],
            ['title' => 'Views & Blade Templating', 'content' => 'Template engine Blade untuk frontend', 'order' => 5, 'is_locked' => true, 'prerequisite_module_id' => null],
        ];

        $laravelModuleObjects = [];
        foreach ($laravelModules as $i => $module) {
            $module['course_id'] = $laravelCourse->id;
            // Set prerequisite: module 3 depends on 2, module 4 depends on 3, module 5 depends on 4
            if ($i > 1 && isset($laravelModuleObjects[$i - 1])) {
                $module['prerequisite_module_id'] = $laravelModuleObjects[$i - 1]->id;
            }
            $created = Module::create($module);
            $laravelModuleObjects[$i] = $created;
        }

        // PHP Advanced Concepts Course
        $phpCourse = Course::where('title', 'PHP Advanced Concepts')->first();
        $phpModules = [
            ['title' => 'OOP Concepts', 'content' => 'Class, Object, Inheritance, Polymorphism, Abstraction', 'order' => 1, 'is_locked' => false, 'prerequisite_module_id' => null],
            ['title' => 'Design Patterns', 'content' => 'Singleton, Factory, Observer, Strategy patterns', 'order' => 2, 'is_locked' => true, 'prerequisite_module_id' => null],
            ['title' => 'Error Handling & Exceptions', 'content' => 'Try-catch, custom exceptions, error logging', 'order' => 3, 'is_locked' => true, 'prerequisite_module_id' => null],
            ['title' => 'Namespaces & Autoloading', 'content' => 'PSR standards, namespace best practices', 'order' => 4, 'is_locked' => true, 'prerequisite_module_id' => null],
        ];

        $phpModuleObjects = [];
        foreach ($phpModules as $i => $module) {
            $module['course_id'] = $phpCourse->id;
            if ($i > 0 && isset($phpModuleObjects[$i - 1])) {
                $module['prerequisite_module_id'] = $phpModuleObjects[$i - 1]->id;
            }
            $created = Module::create($module);
            $phpModuleObjects[$i] = $created;
        }

        // Database Design & SQL Course
        $dbCourse = Course::where('title', 'Database Design & SQL')->first();
        $dbModules = [
            ['title' => 'Database Fundamentals', 'content' => 'Konsep dasar relational database', 'order' => 1, 'is_locked' => false, 'prerequisite_module_id' => null],
            ['title' => 'Schema Design', 'content' => 'Normalization, ER diagram, best practices', 'order' => 2, 'is_locked' => true, 'prerequisite_module_id' => null],
            ['title' => 'Advanced SQL Queries', 'content' => 'JOIN, subquery, window functions, CTE', 'order' => 3, 'is_locked' => true, 'prerequisite_module_id' => null],
            ['title' => 'Query Optimization', 'content' => 'Indexing, execution plans, performance tuning', 'order' => 4, 'is_locked' => true, 'prerequisite_module_id' => null],
        ];

        $dbModuleObjects = [];
        foreach ($dbModules as $i => $module) {
            $module['course_id'] = $dbCourse->id;
            if ($i > 0 && isset($dbModuleObjects[$i - 1])) {
                $module['prerequisite_module_id'] = $dbModuleObjects[$i - 1]->id;
            }
            $created = Module::create($module);
            $dbModuleObjects[$i] = $created;
        }

        // REST API Development Course
        $apiCourse = Course::where('title', 'REST API Development')->first();
        $apiModules = [
            ['title' => 'REST Principles', 'content' => 'HTTP methods, status codes, REST conventions', 'order' => 1, 'is_locked' => false, 'prerequisite_module_id' => null],
            ['title' => 'API Design Best Practices', 'content' => 'Versioning, pagination, filtering, caching', 'order' => 2, 'is_locked' => true, 'prerequisite_module_id' => null],
            ['title' => 'Authentication & Authorization', 'content' => 'JWT, OAuth2, API keys, permissions', 'order' => 3, 'is_locked' => true, 'prerequisite_module_id' => null],
            ['title' => 'API Documentation & Testing', 'content' => 'OpenAPI/Swagger, Postman, unit testing', 'order' => 4, 'is_locked' => true, 'prerequisite_module_id' => null],
        ];

        $apiModuleObjects = [];
        foreach ($apiModules as $i => $module) {
            $module['course_id'] = $apiCourse->id;
            if ($i > 0 && isset($apiModuleObjects[$i - 1])) {
                $module['prerequisite_module_id'] = $apiModuleObjects[$i - 1]->id;
            }
            $created = Module::create($module);
            $apiModuleObjects[$i] = $created;
        }

        // Web Security Course
        $secCourse = Course::where('title', 'Web Security Best Practices')->first();
        $secModules = [
            ['title' => 'Common Vulnerabilities (OWASP Top 10)', 'content' => 'SQL Injection, XSS, CSRF, auth flaws', 'order' => 1, 'is_locked' => false, 'prerequisite_module_id' => null],
            ['title' => 'Input Validation & Sanitization', 'content' => 'Whitelist, blacklist, escaping techniques', 'order' => 2, 'is_locked' => true, 'prerequisite_module_id' => null],
            ['title' => 'Secure Authentication', 'content' => 'Password hashing, 2FA, session management', 'order' => 3, 'is_locked' => true, 'prerequisite_module_id' => null],
            ['title' => 'Data Protection', 'content' => 'Encryption, SSL/TLS, secure headers', 'order' => 4, 'is_locked' => true, 'prerequisite_module_id' => null],
        ];

        foreach ($secModules as $i => $module) {
            $module['course_id'] = $secCourse->id;
            if ($i > 0) {
                $lastModule = Module::where('course_id', $secCourse->id)
                    ->where('order', $i)
                    ->first();
                if ($lastModule) {
                    $module['prerequisite_module_id'] = $lastModule->id;
                }
            }
            Module::create($module);
        }

        echo "\n✅ ModuleSeeder: 21 modules created with prerequisites\n";
    }
}
