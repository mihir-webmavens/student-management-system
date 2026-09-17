<?php

use App\Mcp\Servers\StudentManagementServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('student-management', StudentManagementServer::class);
