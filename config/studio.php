<?php

return [

    'name' => env('STUDIO_NAME', 'C-Net AI Studio'),

    'tagline' => env(
        'STUDIO_TAGLINE',
        'Create • Edit • Enhance • Automate'
    ),

    'domain' => env(
        'STUDIO_DOMAIN',
        'https://studio.mciedu.com'
    ),

    'architecture' => [
        'mode' => 'saas',
        'ai_provider_policy' => 'open-source-first',
        'mandatory_paid_ai' => false,
        'worker_mode' => 'distributed',
        'editor_version' => 'V3',
    ],

    'features' => [
        'text_to_video' => true,
        'image_to_video' => true,
        'business_ads' => true,
        'reels' => true,
        'auto_subtitles' => true,
        'voiceover' => true,
        'background_removal' => true,
        'timeline_editor' => true,
        'templates' => true,
        'cloud_projects' => true,
        'subscription_ready' => true,
        'license_ready' => true,
    ],
];
