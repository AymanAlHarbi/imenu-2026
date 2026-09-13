<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class QRController extends Controller
{
    /** المطبوعات الجاهزة: القالب ← [عنوان، مقاس @page بالمليمتر (Chromium لا يعرف A6 بالاسم)، وصف المقاس] */
    private const TEMPLATES = [
        'window'  => ['title' => 'Window sticker', 'page' => '148mm 210mm', 'size' => 'A5'],
        'counter' => ['title' => 'Counter card',   'page' => '105mm 148mm', 'size' => 'A6'],
        'car'     => ['title' => 'Car sign',       'page' => '210mm 148mm', 'size' => 'A5'],
        'cup'     => ['title' => 'Cup sticker',    'page' => '210mm 297mm', 'size' => '4×4 cm · 15'],
    ];

    public function index(): View
    {
        $vendor = auth()->user()->restorant;
        $linkToTheMenu = $vendor->getLinkAttribute();

        // iMenu 2026 - الإصدار الثاني (تعطيل لا حذف): QR_PAGE_V2=false يعيد الصفحة الأصلية أسفل كما هي
        if (config('settings.qr_page_v2')) {
            $hasLogo = strlen((string) $vendor->logo) > 3;

            return view('qrsaas.qrgen_v2', [
                'vendor'  => $vendor,
                'url'     => $linkToTheMenu,
                'hasLogo' => $hasLogo,
                'config'  => [
                    'url'       => $linkToTheMenu,
                    'slug'      => $vendor->subdomain ?: 'menu',
                    'hasLogo'   => $hasLogo,
                    'logo'      => $hasLogo ? $vendor->logom : null,
                    'printBase' => preg_replace('#/window$#', '', route('qr.print', 'window')),
                    't'         => [
                        'copied'   => __('Copied'),
                        'tooLight' => __('This color is too light to scan reliably.'),
                    ],
                ],
            ]);
        }

        $areas = $vendor->areas()->with('tables')->get()->toArray();
        $tables = [];
        foreach ($areas as $key => $area) {
            foreach ($area['tables'] as $table) {
                $tables[$table['id']] = $area['name'].' - '.$table['name'];
            }
        }

        $dataToPass = [
            'url' => $linkToTheMenu,
            'titleGenerator' => __('Restaurant QR Generators'),
            'selectQRStyle' => __('SELECT QR STYLE'),
            'selectQRColor' => __('SELECT QR COLOR'),
            'color1' => __('Color 1'),
            'color2' => __('Color 2'),
            'titleDownload' => __('QR Downloader'),
            'selectTable' => __('Select Table'),
            'tables' => $tables,
            'allTables' => __('No specific table'),
            'downloadJPG' => __('Download JPG'),
            'titleTemplate' => __('Menu Print template'),
            'downloadPrintTemplates' => __('Download Print Templates'),
            'templates' => explode(',', config('settings.templates')),
            'linkToTemplates' => env('linkToTemplates', '/impactfront/img/templates.zip'),
        ];

        return view('qrsaas.qrgen')->with('data', json_encode($dataToPass));
    }

    /**
     * iMenu 2026 - مطبوعة جاهزة للطباعة باسم المقهى ورمزه، تُطبع من المتصفح.
     * الرمز يحمل ?src=<template> ليُعرف لاحقًا أي موضع جلب العميل.
     */
    public function print(Request $request, string $template): View
    {
        abort_unless(isset(self::TEMPLATES[$template]), 404);

        $vendor = auth()->user()->restorant;
        $url = $vendor->getLinkAttribute();
        $meta = self::TEMPLATES[$template];

        $hex = fn ($v, $default) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $v) ? $v : $default;
        $style = in_array($request->query('s'), ['classic', 'dots', 'logo'], true) ? $request->query('s') : 'dots';
        $hasLogo = strlen((string) $vendor->logo) > 3;

        return view('qrsaas.qrprint', [
            'vendor'   => $vendor,
            'url'      => $url,
            'template' => $template,
            'title'    => __($meta['title']),
            'page'     => $meta['page'],
            'size'     => $meta['size'],
            'config'   => [
                'template' => $template,
                'data'     => $url.(str_contains($url, '?') ? '&' : '?').'src='.$template,
                'style'    => $style === 'logo' && ! $hasLogo ? 'dots' : $style,
                'logo'     => $hasLogo ? $vendor->logom : null,
                'c'        => $hex($request->query('c'), '#16304C'),
                'c2'       => $hex($request->query('c2'), '#16304C'),
            ],
        ]);
    }
}
