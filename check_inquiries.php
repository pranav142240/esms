<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

echo "Existing School Inquiries:\n";
$inquiries = \App\Models\SchoolInquiry::select('id', 'school_email', 'proposed_domain', 'status')->get();

foreach ($inquiries as $inquiry) {
    echo $inquiry->id . ' - ' . $inquiry->school_email . ' - ' . $inquiry->proposed_domain . ' - ' . $inquiry->status . "\n";
}

echo "Total: " . $inquiries->count() . " inquiries\n";
