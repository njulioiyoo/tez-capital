<?php

namespace Database\Seeders;

use App\Models\Education;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EducationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $educationData = [
            [
                'title_id' => 'Panduan Dasar Investasi untuk Pemula',
                'title_en' => 'Basic Investment Guide for Beginners',
                'description_id' => 'Pelajari dasar-dasar investasi yang perlu diketahui oleh setiap pemula',
                'description_en' => 'Learn the basics of investment that every beginner needs to know',
                'content_id' => '<h2>Mengapa Investasi Penting?</h2><p>Investasi adalah cara terbaik untuk membangun kekayaan jangka panjang dan melindungi nilai uang dari inflasi.</p><h3>Langkah Pertama Berinvestasi:</h3><ol><li>Tentukan tujuan finansial</li><li>Pahami profil risiko Anda</li><li>Diversifikasi portofolio</li><li>Mulai dengan jumlah kecil</li></ol>',
                'content_en' => '<h2>Why is Investment Important?</h2><p>Investment is the best way to build long-term wealth and protect money value from inflation.</p><h3>First Steps to Invest:</h3><ol><li>Define your financial goals</li><li>Understand your risk profile</li><li>Diversify your portfolio</li><li>Start with small amounts</li></ol>',
                'category' => 'tutorial',
                'tags' => ['investment', 'beginner', 'finance', 'tutorial'],
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(10),
                'meta_title_id' => 'Panduan Investasi Pemula - TEZ Capital',
                'meta_title_en' => 'Beginner Investment Guide - TEZ Capital',
                'meta_description_id' => 'Panduan lengkap investasi untuk pemula, mulai dari dasar hingga strategi terbaik',
                'meta_description_en' => 'Complete investment guide for beginners, from basics to best strategies',
                'sort_order' => 1,
                'view_count' => 156,
            ],
            [
                'title_id' => 'Tips Mengelola Keuangan Personal',
                'title_en' => 'Personal Financial Management Tips',
                'description_id' => 'Strategi praktis untuk mengelola keuangan personal dengan efektif',
                'description_en' => 'Practical strategies for managing personal finances effectively',
                'content_id' => '<h2>Prinsip Dasar Pengelolaan Keuangan</h2><p>Mengelola keuangan personal yang baik dimulai dari pemahaman tentang pendapatan dan pengeluaran.</p><h3>Tips Praktis:</h3><ul><li>Buat anggaran bulanan</li><li>Pisahkan kebutuhan dan keinginan</li><li>Sisihkan 20% untuk tabungan</li><li>Hindari hutang konsumtif</li></ul>',
                'content_en' => '<h2>Basic Principles of Financial Management</h2><p>Good personal financial management starts with understanding income and expenses.</p><h3>Practical Tips:</h3><ul><li>Create monthly budget</li><li>Separate needs and wants</li><li>Save 20% for savings</li><li>Avoid consumptive debt</li></ul>',
                'category' => 'tips',
                'tags' => ['personal finance', 'budgeting', 'money management'],
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(7),
                'meta_title_id' => 'Tips Mengelola Keuangan Personal - TEZ Capital',
                'meta_title_en' => 'Personal Financial Management Tips - TEZ Capital',
                'meta_description_id' => 'Tips praktis mengelola keuangan personal untuk hidup yang lebih sejahtera',
                'meta_description_en' => 'Practical tips for managing personal finances for a more prosperous life',
                'sort_order' => 2,
                'view_count' => 89,
            ],
            [
                'title_id' => 'Memahami Risiko dalam Investasi',
                'title_en' => 'Understanding Investment Risks',
                'description_id' => 'Pelajari berbagai jenis risiko investasi dan cara mengelolanya',
                'description_en' => 'Learn about various types of investment risks and how to manage them',
                'content_id' => '<h2>Jenis-jenis Risiko Investasi</h2><p>Setiap investasi memiliki risiko. Penting untuk memahami dan mengelola risiko tersebut.</p><h3>Kategori Risiko:</h3><ol><li>Risiko Pasar</li><li>Risiko Kredit</li><li>Risiko Likuiditas</li><li>Risiko Inflasi</li></ol>',
                'content_en' => '<h2>Types of Investment Risks</h2><p>Every investment has risks. It\'s important to understand and manage these risks.</p><h3>Risk Categories:</h3><ol><li>Market Risk</li><li>Credit Risk</li><li>Liquidity Risk</li><li>Inflation Risk</li></ol>',
                'category' => 'guide',
                'tags' => ['risk management', 'investment', 'education'],
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(5),
                'meta_title_id' => 'Memahami Risiko Investasi - TEZ Capital',
                'meta_title_en' => 'Understanding Investment Risks - TEZ Capital',
                'meta_description_id' => 'Panduan lengkap memahami dan mengelola risiko dalam investasi',
                'meta_description_en' => 'Complete guide to understanding and managing investment risks',
                'sort_order' => 3,
                'view_count' => 234,
            ],
            [
                'title_id' => 'Platform Digital untuk Investasi Modern',
                'title_en' => 'Digital Platforms for Modern Investment',
                'description_id' => 'Eksplorasi platform digital yang memudahkan investasi di era modern',
                'description_en' => 'Explore digital platforms that make investment easier in the modern era',
                'content_id' => '<h2>Revolusi Digital dalam Investasi</h2><p>Platform digital telah mengubah cara kita berinvestasi, membuatnya lebih mudah dan terjangkau.</p><h3>Keuntungan Platform Digital:</h3><ul><li>Biaya transaksi rendah</li><li>Akses 24/7</li><li>Diversifikasi mudah</li><li>Transparansi tinggi</li></ul>',
                'content_en' => '<h2>Digital Revolution in Investment</h2><p>Digital platforms have changed how we invest, making it easier and more affordable.</p><h3>Benefits of Digital Platforms:</h3><ul><li>Low transaction costs</li><li>24/7 access</li><li>Easy diversification</li><li>High transparency</li></ul>',
                'category' => 'news',
                'tags' => ['fintech', 'digital', 'modern investment', 'technology'],
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(2),
                'meta_title_id' => 'Platform Digital Investasi Modern - TEZ Capital',
                'meta_title_en' => 'Modern Digital Investment Platforms - TEZ Capital',
                'meta_description_id' => 'Pelajari platform digital terbaik untuk investasi modern yang aman dan profitable',
                'meta_description_en' => 'Learn about the best digital platforms for safe and profitable modern investment',
                'sort_order' => 4,
                'view_count' => 67,
            ],
            [
                'title_id' => 'Pengumuman: Fitur Baru TEZ Capital',
                'title_en' => 'Announcement: New TEZ Capital Features',
                'description_id' => 'Kami dengan bangga memperkenalkan fitur-fitur terbaru di platform TEZ Capital',
                'description_en' => 'We proudly introduce the latest features on the TEZ Capital platform',
                'content_id' => '<h2>Fitur Baru yang Kami Hadirkan</h2><p>Dalam upaya memberikan pengalaman terbaik bagi pengguna, kami telah menambahkan beberapa fitur baru.</p><h3>Fitur Utama:</h3><ul><li>Dashboard analitik real-time</li><li>Notifikasi cerdas</li><li>Portfolio tracker</li><li>Educational hub</li></ul>',
                'content_en' => '<h2>New Features We Present</h2><p>In an effort to provide the best experience for users, we have added several new features.</p><h3>Main Features:</h3><ul><li>Real-time analytics dashboard</li><li>Smart notifications</li><li>Portfolio tracker</li><li>Educational hub</li></ul>',
                'category' => 'announcement',
                'tags' => ['announcement', 'new features', 'platform update'],
                'is_published' => false,
                'published_at' => null,
                'meta_title_id' => 'Fitur Baru TEZ Capital - Platform Investasi',
                'meta_title_en' => 'New TEZ Capital Features - Investment Platform',
                'meta_description_id' => 'Temukan fitur-fitur terbaru di platform TEZ Capital untuk pengalaman investasi yang lebih baik',
                'meta_description_en' => 'Discover the latest features on the TEZ Capital platform for a better investment experience',
                'sort_order' => 5,
                'view_count' => 0,
            ],
        ];

        foreach ($educationData as $data) {
            Education::create($data);
        }
    }
}
