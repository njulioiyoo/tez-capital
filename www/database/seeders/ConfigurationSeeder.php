<?php

namespace Database\Seeders;

use App\Models\Configuration;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configurations = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'TEZ Capital',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application name',
                'is_public' => true,
            ],
            [
                'key' => 'app_description',
                'value' => 'Platform keuangan digital terpercaya',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Application description',
                'is_public' => true,
            ],
            [
                'key' => 'app_timezone',
                'value' => 'Asia/Jakarta',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application timezone',
                'is_public' => false,
            ],

            // Branding
            [
                'key' => 'company_name',
                'value' => 'Tez Capital Dashboard',
                'type' => 'string',
                'group' => 'branding',
                'description' => 'Company name',
                'is_public' => true,
            ],
            [
                'key' => 'company_logo',
                'value' => null,
                'type' => 'file',
                'group' => 'branding',
                'description' => 'Company logo image',
                'is_public' => true,
            ],
            [
                'key' => 'company_logo_dark',
                'value' => null,
                'type' => 'file',
                'group' => 'branding',
                'description' => 'Company logo for dark theme',
                'is_public' => true,
            ],
            [
                'key' => 'favicon',
                'value' => null,
                'type' => 'file',
                'group' => 'branding',
                'description' => 'Website favicon',
                'is_public' => true,
            ],

            // Homepage
            [
                'key' => 'homepage_hero_title',
                'value' => 'Solusi Keuangan Digital Terdepan',
                'type' => 'string',
                'group' => 'homepage',
                'description' => 'Homepage hero section title',
                'is_public' => true,
            ],
            [
                'key' => 'homepage_hero_subtitle',
                'value' => 'Platform yang menghubungkan investor dan borrower dengan sistem kredit yang aman dan transparan',
                'type' => 'text',
                'group' => 'homepage',
                'description' => 'Homepage hero section subtitle',
                'is_public' => true,
            ],
            [
                'key' => 'homepage_banners',
                'value' => '[]',
                'type' => 'json',
                'group' => 'homepage',
                'description' => 'Homepage banner slides (array of image paths)',
                'is_public' => true,
            ],
            [
                'key' => 'homepage_features',
                'value' => '[{"title":"Aman & Terpercaya","description":"Sistem keamanan berlapis dengan teknologi enkripsi terdepan","icon":"shield"},{"title":"Bunga Kompetitif","description":"Dapatkan return investasi hingga 15% per tahun","icon":"trending-up"},{"title":"Proses Cepat","description":"Approval dalam 24 jam dengan persyaratan yang mudah","icon":"clock"}]',
                'type' => 'json',
                'group' => 'homepage',
                'description' => 'Homepage features section',
                'is_public' => true,
            ],

            // Credit Settings
            [
                'key' => 'credit_min_amount',
                'value' => 1000000,
                'type' => 'integer',
                'group' => 'credit',
                'description' => 'Minimum credit amount (IDR)',
                'is_public' => true,
            ],
            [
                'key' => 'credit_max_amount',
                'value' => 500000000,
                'type' => 'integer',
                'group' => 'credit',
                'description' => 'Maximum credit amount (IDR)',
                'is_public' => true,
            ],
            [
                'key' => 'credit_tenors',
                'value' => '[6,12,18,24,36,48]',
                'type' => 'json',
                'group' => 'credit',
                'description' => 'Available credit tenors (months)',
                'is_public' => true,
            ],
            [
                'key' => 'credit_interest_rates',
                'value' => '{"6":12,"12":13,"18":14,"24":15,"36":16,"48":17}',
                'type' => 'json',
                'group' => 'credit',
                'description' => 'Interest rates by tenor (percentage)',
                'is_public' => true,
            ],
            [
                'key' => 'credit_admin_fee',
                'value' => 2.5,
                'type' => 'string',
                'group' => 'credit',
                'description' => 'Admin fee percentage',
                'is_public' => true,
            ],

            // Maintenance
            [
                'key' => 'maintenance_mode',
                'value' => false,
                'type' => 'boolean',
                'group' => 'maintenance',
                'description' => 'Enable maintenance mode',
                'is_public' => true,
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'Situs sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.',
                'type' => 'text',
                'group' => 'maintenance',
                'description' => 'Maintenance mode message',
                'is_public' => true,
            ],
            [
                'key' => 'maintenance_end_time',
                'value' => null,
                'type' => 'string',
                'group' => 'maintenance',
                'description' => 'Estimated maintenance end time',
                'is_public' => true,
            ],

            // Contact
            [
                'key' => 'contact_phone',
                'value' => '+62 21 1234 5678',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Company phone number',
                'is_public' => true,
            ],
            [
                'key' => 'contact_email',
                'value' => 'support@tezcapital.com',
                'type' => 'email',
                'group' => 'contact',
                'description' => 'Company email address',
                'is_public' => true,
            ],
            [
                'key' => 'contact_address',
                'value' => 'Jl. Sudirman No. 123, Jakarta Pusat 10220',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Company address',
                'is_public' => true,
            ],
            [
                'key' => 'contact_whatsapp',
                'value' => '+62 812 3456 7890',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'WhatsApp contact number',
                'is_public' => true,
            ],
            [
                'key' => 'social_media',
                'value' => '{"facebook":"https://facebook.com/tezcapital","instagram":"https://instagram.com/tezcapital","twitter":"https://twitter.com/tezcapital","linkedin":"https://linkedin.com/company/tezcapital"}',
                'type' => 'json',
                'group' => 'contact',
                'description' => 'Social media links',
                'is_public' => true,
            ],

            // Language Settings (Bilingual: Indonesian & English)
            [
                'key' => 'bilingual_enabled',
                'value' => false,
                'type' => 'boolean',
                'group' => 'language',
                'description' => 'Enable bilingual support (Indonesian & English)',
                'is_public' => true,
            ],
            [
                'key' => 'default_language',
                'value' => 'id',
                'type' => 'string',
                'group' => 'language',
                'description' => 'Default application language (id/en)',
                'is_public' => true,
            ],
            [
                'key' => 'language_switcher_enabled',
                'value' => true,
                'type' => 'boolean',
                'group' => 'language',
                'description' => 'Show language switcher in frontend',
                'is_public' => true,
            ],
            [
                'key' => 'auto_detect_language',
                'value' => false,
                'type' => 'boolean',
                'group' => 'language',
                'description' => 'Auto-detect user language from browser',
                'is_public' => true,
            ],
        ];

        foreach ($configurations as $config) {
            Configuration::updateOrCreate(
                ['key' => $config['key']],
                $config
            );
        }
    }
}
