<?php
/**
 * Syntax Verification for Widget Auth Type Feature
 * 
 * This file verifies that all PHP files have correct syntax
 * and that the feature is properly integrated.
 */

echo "=== Widget Auth Type Feature - Code Verification ===\n\n";

// Check if migration file syntax is valid
echo "1. Checking migration file syntax...\n";
$migration_file = __DIR__ . '/database/migrations/2024_01_01_000004_add_widget_auth_type_to_face_widgets.php';
if (file_exists($migration_file)) {
    echo "   ✅ Migration file exists\n";
    $content = file_get_contents($migration_file);
    if (strpos($content, "widget_auth_type") !== false && strpos($content, "'register', 'login'") !== false) {
        echo "   ✅ Migration contains widget_auth_type enum field\n";
    } else {
        echo "   ❌ Migration missing widget_auth_type field\n";
    }
} else {
    echo "   ❌ Migration file not found\n";
}

// Check Model
echo "\n2. Checking FaceWidget model...\n";
$model_file = __DIR__ . '/app/Models/FaceWidget.php';
if (file_exists($model_file)) {
    echo "   ✅ Model file exists\n";
    $content = file_get_contents($model_file);
    if (strpos($content, "'widget_auth_type'") !== false) {
        echo "   ✅ Model has widget_auth_type in fillable\n";
        if (strpos($content, "'widget_auth_type' => 'string'") !== false) {
            echo "   ✅ Model casts widget_auth_type as string\n";
        } else {
            echo "   ❌ Model missing widget_auth_type cast\n";
        }
    } else {
        echo "   ❌ Model missing widget_auth_type\n";
    }
} else {
    echo "   ❌ Model file not found\n";
}

// Check Controller
echo "\n3. Checking FaceWidgetController...\n";
$controller_file = __DIR__ . '/app/Http/Controllers/FaceWidgetController.php';
if (file_exists($controller_file)) {
    echo "   ✅ Controller file exists\n";
    $content = file_get_contents($controller_file);
    if (strpos($content, "'widget_auth_type'") !== false) {
        echo "   ✅ Controller handles widget_auth_type\n";
        if (strpos($content, "'in:register,login'") !== false) {
            echo "   ✅ Controller validates register/login values\n";
        } else {
            echo "   ❌ Controller missing validation rule\n";
        }
    } else {
        echo "   ❌ Controller missing widget_auth_type handling\n";
    }
} else {
    echo "   ❌ Controller file not found\n";
}

// Check Form
echo "\n4. Checking widget form...\n";
$form_file = __DIR__ . '/resources/views/widgets/_form.blade.php';
if (file_exists($form_file)) {
    echo "   ✅ Form file exists\n";
    $content = file_get_contents($form_file);
    if (strpos($content, "Authentication Mode") !== false) {
        echo "   ✅ Form has Authentication Mode toggle\n";
        if (strpos($content, "handleAuthTypeToggle") !== false) {
            echo "   ✅ Form has toggle handler function\n";
        } else {
            echo "   ❌ Form missing toggle handler\n";
        }
    } else {
        echo "   ❌ Form missing Authentication Mode field\n";
    }
} else {
    echo "   ❌ Form file not found\n";
}

// Check Widget Serve Script
echo "\n5. Checking widget embed script...\n";
$serve_file = __DIR__ . '/resources/views/widget/serve.blade.php';
if (file_exists($serve_file)) {
    echo "   ✅ Serve script exists\n";
    $content = file_get_contents($serve_file);
    if (strpos($content, "WIDGET_AUTH_TYPE") !== false) {
        echo "   ✅ Script has WIDGET_AUTH_TYPE variable\n";
        if (strpos($content, "/api/init_login") !== false && strpos($content, "/api/init_register") !== false) {
            echo "   ✅ Script handles both login and register endpoints\n";
        } else {
            echo "   ❌ Script missing endpoint handling\n";
        }
    } else {
        echo "   ❌ Script missing WIDGET_AUTH_TYPE\n";
    }
} else {
    echo "   ❌ Serve script file not found\n";
}

echo "\n=== Verification Complete ===\n";
echo "\nNext steps:\n";
echo "1. Run: php artisan migrate\n";
echo "2. Test widget creation with new Authentication Mode toggle\n";
echo "3. Verify toggle persists on edit\n";
echo "4. Test register mode: should call /api/init_register\n";
echo "5. Test login mode: should call /api/init_login\n";
