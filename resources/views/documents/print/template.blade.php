<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ໜັງສືສະເໜີ - {{ $document->document_code }}</title>
    <link rel="icon" href="{{ asset('fns_Logo.ico') }}" type="image/x-icon">

    <!-- ດຶງ ຟອນ ລາວ ຈາກ Google Fonts ເພື່ອໃຫ້ສະແດງຜົນງາມ -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">

    <style>
        @page {
            size: A4;
            margin: 1.5cm 2cm 1.5cm 2.5cm; /* ເທິງ, ຂວາ, ລຸ່ມ, ຊ້າຍ (ເຜື່ອຊ້າຍໄວ້ເຈາະຮູເຂົ້າເຫຼັ້ມ) */
        }
        * {
            font-family: 'Noto Sans Lao', 'Saysettha OT', sans-serif;
            box-sizing: border-box;
        }
        body {
            font-size: 12pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        p { margin: 0; padding: 0; }
        
        .header-container { text-align: center; margin-bottom: 20px; }
        .header-container img { margin: 0; padding: 0; }
        .page-break { page-break-before: always; }

        .header-lao { font-size: 14pt; font-weight: bold; margin-top: 5px; }
        .header-motto { font-size: 12pt; font-weight: bold; }
        .main-title { font-size: 16pt; font-weight: bold; margin: 20px 0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { 
            border: 1px solid black; 
            padding: 4px 5px; 
            text-align: left; 
            vertical-align: top;
        }
        th { text-align: center; font-weight: bold; }

        /* ຊ່ອນປຸ່ມພີມຕອນທີ່ພີມເອກະສານຈິງ ຫຼື ບັນທຶກເປັນ PDF */
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; } /* ໃຫ້ພິມສີພື້ນຫຼັງອອກມານຳ ຖ້າມີ */
        }
    </style>
</head>
<body>

    <!-- ປຸ່ມສັ່ງພີມເອກະສານ (ຈະບໍ່ສະແດງຕອນພີມອອກມາ) -->
    <div class="no-print" style="position: fixed; top: 15px; right: 15px; z-index: 9999; background: #ffffff; padding: 10px; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: #2563eb; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
            🖨️ ພິມເອກະສານ / ບັນທຶກເປັນ PDF
        </button>
    </div>

    {{-- ສ່ວນຫົວເອກະສານ --}}
    <div class="header-container">
        @if(file_exists(public_path('images/emblem.png')))
            <img src="{{ asset('images/emblem.png') }}" style="width: 80px; height: auto;">
        @endif
        <p class="header-lao">ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</p>
        <p class="header-motto">ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</p>
    </div>
    
    <div style="margin-top: 20px; margin-bottom: 10px;">
        <div style="float: left; width: 50%;">
            <p>ຄະນະວິທະຍາສາດທຳມະຊາດ</p>
            <p>{{ $document->requester->department->name ?? '' }}</p>
        </div>
        <div style="float: right; width: 50%; text-align: right;">
            <p>ເລກທີ: {{ substr($document->document_code, 0, 3) }}-{{ substr($document->document_code, 4, 2) }}/{{ getDepartmentAbbreviation($document->requester->department->name ?? '') }}</p>
            <p>{{ formatLaoDate($document->created_at) }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <h1 class="main-title text-center">ໜັງສືສະເໜີ</h1>
    <p style="margin-top: 15px; text-indent: 80px;">ຮຽນ: ທ່ານ ຫົວໜ້າຄະນະວິຊາ ຄະນະວິທະຍາສາດທຳມະຊາດ ທີ່ນັບຖື</p>
    <p style="text-indent: 80px;">ເລື່ອງ: {{ $document->title }}</p>
    @if($document->references)
        <div style="padding-left: 40px;"> 
            <table style="width: 100%; border: none; margin-top: 5px;">
                <tr style="vertical-align: top;">
                    {{-- 1. ເພີ່ມ text-align: left; ໃນ td ຂອງຄໍາວ່າ ອີງຕາມ --}}
                    <td style="border: none; padding: 0; padding-right: 10px; white-space: nowrap; text-align: right;">
                        <span style="font-weight: bold;">ອີງຕາມ:</span>
                    </td>
                    {{-- 2. ເພີ່ມ text-align: left; ໃນ td ຂອງເນື້ອໃນ --}}
                    <td style="border: none; padding: 0; text-align: left;">
                        {!! nl2br(e($document->references)) !!}
                    </td>
                </tr>
            </table>
        </div>
    @endif
    <!--@if($document->references)
        <div style="padding-left: 40px;"> 
        <table style="width: 100%; border: none; margin-top: 5px;">
            <tr style="vertical-align: top;">
                <td style="border: none; padding: 0; padding-right: 10px; white-space: nowrap;">
                    <span style="font-weight: bold;">ອີງຕາມ</span>
                </td>
                <td style="border: none; padding: 0;">
                    {!! nl2br(e($document->references)) !!}
                </td>
            </tr>
        </table>
    </div>
    @endif-->
    <!--
    <p style="margin-top: 15px; text-indent: 50px;">ຮຽນ: ທ່ານ ຫົວໜ້າຄະນະວິຊາ ຄະນະວິທະຍາສາດທຳມະຊາດ ທີ່ນັບຖື</p>
    
    <div style="display: flex; margin-top: 5px;">
        <div style="width: 50px; text-align: right; padding-right: 10px;">ເລື່ອງ:</div>
        <div style="flex: 1;">{{ $document->title }}</div>
    </div>

    @if($document->references)
    <div style="display: flex; margin-top: 5px;">
        <div style="width: 50px; text-align: right; padding-right: 10px; font-weight: bold;">ອີງຕາມ:</div>
        <div style="flex: 1;">{!! nl2br(e($document->references)) !!}</div>
    </div>
    @endif
-->
    <div style="margin-top: 15px; text-indent: 50px; text-align: justify;"> 
        <p>
            ຫົວໜ້າ{{ $document->requester->department->name ?? '' }} ຂໍຖືເປັນກຽດ ຮຽນສະເໜີມາຍັງທ່ານ 
            @if($document->document_type_id == 1)
                ເພື່ອຂໍຖອນເງິນຮັບໃຊ້ {{ $document->activity_description }} ເປັນຈຳນວນເງິນທັງໝົດ <b>{{ number_format($document->total_amount, 2) }} ກີບ</b>,
                @if($document->documentItems->count() > 5)
                    ຕາມລາຍລະອຽດໃນຕາຕະລາງຄັດຕິດມາພ້ອມນີ້.
                @else
                    ຕາມລາຍລະອຽດລຸ່ມນີ້:
                @endif
            @elseif($document->document_type_id == 2)
                ເພື່ອຂໍ{{ $document->activity_description }}.
            @endif
        </p>
    </div>
    
    {{-- ຕາຕະລາງນ້ອຍກວ່າ 5 ລາຍການ --}}
    @if($document->document_type_id == 1 && $document->documentItems->count() <= 5 && $document->documentItems->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">ລຳດັບ</th>
                    <th style="width: 45%;">ເນື້ອໃນລາຍການ</th>
                    <th style="width: 10%;">ຈຳນວນ</th>
                    <th style="width: 15%;">ລາຄາຕໍ່ໜ່ວຍ</th>
                    <th style="width: 20%;">ລາຄາລວມ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($document->documentItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->item_description }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0) }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-center font-bold">ມູນຄ່າລວມທັງໝົດ</td>
                    <td></td>
                    <td></td>
                    <td class="text-right font-bold">{{ number_format($document->total_amount, 0) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif
    
    <p style="margin-top: 15px; text-indent: 50px;">ດັ່ງນັ້ນ, ຈຶ່ງສະເໜີມາຍັງທ່ານ ເພື່ອພິຈາລະນາຕາມຄວາມເໝາະສົມດ້ວຍ.</p>
    <p style="margin-top: 10px; text-indent: 30%;">ຮຽນມາດ້ວຍຄວາມເຄົາລົບ ແລະ ນັບຖືເປັນຢ່າງສູງ</p>
    
    {{-- ບ່ອນເຊັນ --}}
    <div style="margin-top: 20px;">
        @if($document->document_type_id == 1)
            {{-- ຂໍຖອນເງິນ -> 3 ບ່ອນເຊັນ --}}
            <div style="float: left; width: 33%; text-align: center;">
                <p class="font-bold">ເຊັນແທນຫົວໜ້າຄະນະວິຊາ<br>ຮອງຫົວໜ້າຄະນະວິຊາ</p>
                <br><br><br>
                <p>.........................</p>
            </div>
            <div style="float: left; width: 33%; text-align: center;">
                <p class="font-bold">ນາຍບັນຊີ</p>
                <br><br><br><br>
                <p>.........................</p>
            </div>
            <div style="float: left; width: 33%; text-align: center;">
                <p class="font-bold">ຫົວໜ້າ{{ getDepartmentType($document->requester->department->name ?? '') }}</p>
                <br><br><br><br>
                <p>.........................</p>
            </div>
        @else
            {{-- ຂໍຈັດຊື້/ສ້ອມແປງ -> 2 ບ່ອນເຊັນ --}}
            <div style="float: left; width: 50%; text-align: center;">
                <p class="font-bold">ເຊັນແທນຫົວໜ້າຄະນະວິຊາ<br>ຮອງຫົວໜ້າຄະນະວິຊາ</p>
                <br><br><br>
                <p>.........................</p>
            </div>
            <div style="float: right; width: 50%; text-align: center;">
                <p class="font-bold">ຫົວໜ້າ{{ getDepartmentType($document->requester->department->name ?? '') }}</p>
                <br><br><br><br>
                <p>.........................</p>
            </div>
        @endif
        <div style="clear: both;"></div>
    </div>

    {{-- ໜ້າທີ 2: ຕາຕະລາງຫຼາຍກວ່າ 5 ລາຍການ --}}
    @if($document->document_type_id == 1 && $document->documentItems->count() > 5)
        <div class="page-break"></div>
        <h2 class="text-center" style="margin-bottom: 20px;">ລາຍລະອຽດການຈ່າຍເງິນ {{ $document->title }}</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">ລຳດັບ</th>
                    <th style="width: 45%;">ເນື້ອໃນລາຍການ</th>
                    <th style="width: 10%;">ຈຳນວນ</th>
                    <th style="width: 15%;">ລາຄາຕໍ່ໜ່ວຍ</th>
                    <th style="width: 20%;">ລາຄາລວມ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($document->documentItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->item_description }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0) }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-center font-bold">ມູນຄ່າລວມທັງໝົດ</td>
                    <td></td>
                    <td></td>
                    <td class="text-right font-bold">{{ number_format($document->total_amount, 0) }}</td>
                </tr>
            </tfoot>
        </table>
        
        <div style="margin-top: 50px; text-align: right; padding-right: 10%;">
            <p class="font-bold">ລາຍເຊັນຜູ້ຄິດໄລ່</p>
            <br><br><br>
            <p>.........................</p>
        </div>
    @endif

    <!-- ສັ່ງໃຫ້ເປີດ Print Dialog ອັດໂນມັດເມື່ອເປີດໜ້ານີ້ -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 800); // ໜ່ວງເວລາໜ້ອຍໜຶ່ງເພື່ອໃຫ້ໂຫຼດຟອນສຳເລັດ
        }
    </script>
</body>
</html>