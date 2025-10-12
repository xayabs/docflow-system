<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ໜັງສືສະເໜີ - {{ $document->document_code }}</title>

    <style>
        @page {
            margin: 1cm 2cm 1cm 2cm; /* ເທິງ, ຂວາ, ລຸ່ມ, ຊ້າຍ */
        }
        @font-face {
            font-family: 'Saysettha OT';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Saysettha OT.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'Saysettha OT';
            font-style: normal;
            font-weight: bold;
            src: url("{{ public_path('fonts/Saysettha OT.ttf') }}") format('truetype');
        }
        * {
            font-family: 'Saysettha OT', sans-serif;
        }
        body {
            font-size: 12pt;
            line-height: 1.00;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        p { margin: 0; padding: 0; }
        
        .header-container {
            text-align: center;
        }
        .header-container img {
            margin: 0; /* 2. ลดระยะห่าง */
            padding: 0;
        }
        .header-container p {
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-before: always;
        }

        .header-lao { font-size: 14pt; font-weight: bold; }
        .header-motto { font-size: 12pt; font-weight: bold; }
        .main-title { font-size: 16pt; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        /*th, td { border: 1px solid black; padding: 5px; text-align: left; }*/
        th, td { 
            border: 1px solid black; 
            padding-top: 0px;     /* Padding ด้านบน */
            padding-bottom: 0px;  /* Padding ด้านล่าง */
            padding-left: 5px;    /* Padding ด้านซ้าย (คงเดิม) */
            padding-right: 5px;   /* Padding ด้านขวา (คงเดิม) */
            text-align: left; 
            vertical-align: top;
        }
        th { text-align: center; font-weight: bold; }
    </style>
</head>
<body>
    {{-- 3. จัดตราแผ่นดินและคำขวัญให้อยู่ตรงกลาง --}}
    <div class="header-container lao-text">
        <img src="{{ public_path('images/emblem.png') }}" style="width: 100px; height: auto;">
        <p class="header-lao">ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</p>
        <p class="header-motto">ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</p>
    </div>
    
    <div style="margin-top: 20px; margin-bottom: 10px;">
    {{-- แถวที่ 1: ชื่อคณะ และ เลขที่ --}}
        <div style="margin-bottom: 5px;">
            <div style="float: left; width: 50%;">
                <p style="font-weight: bold;">ຄະນະວິທະຍາສາດທຳມະຊາດ</p>
            </div>
            <div style="clear: both;"></div>
        </div>

        {{-- แถวที่ 2: ชื่อภาคส่วน และ วันที่ --}}
        <div>
            <div style="float: left; width: 50%;">
                <p>{{ $document->requester->department->name }}</p>
            </div>
            <div style="float: right; width: 45%; text-align: right;">
                <p>ເລກທີ: {{ substr($document->document_code, 0, 3) }}-{{ substr($document->document_code, 4, 2) }}/{{ getDepartmentAbbreviation($document->requester->department->name) }}</p>
            </div>
            <div style="clear: both;"></div>
        </div>
        <div>
            <div style="float: right; width: 45%; text-align: right;">
                <p>{{ formatLaoDate($document->created_at) }}</p>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

    <h1 class="main-title text-center lao-text">ໜັງສືສະເໜີ</h1>
    
    <p style="margin-top: 15px; text-indent: 80px;">ຮຽນ: ທ່ານ ຄະນະບໍດີ ຄະນະວິທະຍາສາດທຳມະຊາດ ທີ່ນັບຖື</p>
    <p style="text-indent: 80px;">ເລື່ອງ: {{ $document->title }}</p>
    @if($document->references)
        <div style="padding-left: 40px;"> 
        <table style="width: 100%; border: none; margin-top: 5px;">
            <tr style="vertical-align: top;">
                {{-- คอลัมน์สำหรับคำว่า "ອີງຕາມ" --}}
                <td style="border: none; padding: 0; padding-right: 10px; white-space: nowrap;">
                    <span style="font-weight: bold;">ອີງຕາມ</span>
                </td>
                {{-- คอลัมน์สำหรับเนื้อหา --}}
                <td style="border: none; padding: 0;">
                    {!! nl2br(e($document->references)) !!}
                </td>
            </tr>
        </table>
    </div>
    @endif

    <div style="margin-top: 20px; text-indent: 40px; text-align: justify;"> 
        <p>
            ຫົວໜ້າ{{ $document->requester->department->name }} ຂໍຖືເປັນກຽດ ຮຽນສະເໜີມາຍັງທ່ານ
            {{-- ตรวจสอบประเภทแล้วต่อท้ายประโยค --}}
            @if($document->document_type_id == 1) {{-- ຂໍຖອນເງິນ --}}
                ເພື່ອຂໍຖອນເງິນຮັບໃຊ້ {{ $document->activity_description }} ເປັນຈຳນວນເງິນທັງໝົດ {{ number_format($document->total_amount, 2) }} ກີບ,
                @if($document->documentItems->count() > 5)
                    ຕາມລາຍລະອຽດໃນຕາຕະລາງຄັດຕິດມາພ້ອມນີ້.
                @else
                    ຕາມລາຍລະອຽດລຸ່ມນີ້:
                @endif
            @elseif($document->document_type_id == 2) {{-- ຂໍຈັດຊື້ --}}
                ເພື່ອຂໍ{{ getActionVerbFromTitle($document->title) }} {{ $document->activity_description }}.
            @endif
        </p>
    </div>
    
    {{-- 5. แก้ไขตาราง --}}
    @if($document->document_type_id == 1 && $document->documentItems->count() <= 5 && $document->documentItems->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ລຳດັບ</th>
                    <th>ເນື້ອໃນລາຍການ</th>
                    <th>ຈຳນວນ</th>
                    <th>ລາຄາຕໍ່ໜ່ວຍ</th>
                    <th>ລາຄາລວມ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($document->documentItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->item_description }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            {{--(tfoot) --}}
            <tfoot>
                <tr>
                    <td colspan="2" class="text-center font-bold">ມູນຄ່າລວມທັງໝົດ</td>
                    <td></td> {{-- เซลล์ว่างสำหรับ "จำนวน" --}}
                    <td></td> {{-- เซลล์ว่างสำหรับ "ราคาต่อหน่วย" --}}
                    <td class="text-right font-bold">{{ number_format($document->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif
    
    <p style="margin-top: 15px; text-indent: 120px;">ດັ່ງນັ້ນ, ຈຶ່ງສະເໜີມາຍັງທ່ານ ເພື່ອພິຈາລະນາຕາມຄວາມເໝາະສົມດ້ວຍ.</p>
    <p style="margin-top: 10px; text-indent: 240px;">ຮຽນມາດ້ວຍຄວາມເຄົາລົບ ແລະ ນັບຖືເປັນຢ່າງສູງ</p>
    
    <div class="lao-text" style="margin-top: 20px; float: right; width: 45%; text-align: center;">
        <p class="font-bold">ຫົວໜ້າ{{ getDepartmentType($document->requester->department->name) }}</p>
        <br><br>
        <p>.........................</p>
    </div>
    <div style="clear: both;"></div>

    @if($document->document_type_id == 1 && $document->documentItems->count() > 5)
        <div class="page-break"></div>
        <h2 class="text-center" style="margin-bottom: 20px;">ລາຍລະອຽດການຈ່າຍເງິນ {{ $document->title }}</h2>
        <table>
            <thead>
                <tr>
                    <th>ລຳດັບ</th>
                    <th>ເນື້ອໃນລາຍການ</th>
                    <th>ຈຳນວນ</th>
                    <th>ລາຄາຕໍ່ໜ່ວຍ</th>
                    <th>ລາຄາລວມ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($document->documentItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->item_description }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
                {{--(tfoot) --}}
            <tfoot>
                <tr>
                    <td colspan="2" class="text-center font-bold">ມູນຄ່າລວມທັງໝົດ</td>
                    <td></td> {{-- เซลล์ว่างสำหรับ "จำนวน" --}}
                    <td></td> {{-- เซลล์ว่างสำหรับ "ราคาต่อหน่วย" --}}
                    <td class="text-right font-bold">{{ number_format($document->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        <div class="lao-text" style="margin-top: 50px; float: right; width: 45%; text-align: center;">
            <p class="font-bold">ລາຍເຊັນຜູ້ຄິດໄລ່</p>
            <br><br><br>
            <p>.........................</p>
    </div>
    @endif

</body>
</html>