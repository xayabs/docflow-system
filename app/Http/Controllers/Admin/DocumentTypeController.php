<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function index()
    {
        $documentTypes = DocumentType::orderBy('name')->get(); // <-- ປ່ຽນຊື່ຕົວແປ
        return view('admin.document-types.index', compact('documentTypes')); // <-- ປ່ຽນ path ຂອງ view
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name', // <-- ປ່ຽນຊື່ຕາຕະລາງ
        ]);

        DocumentType::create($request->only('name')); // <-- ປ່ຽນ Model

        return redirect()->route('admin.document-types.index')->with('success', 'ເພີ່ມປະເພດເອກະສານໃໝ່ສຳເລັດແລ້ວ.'); // <-- ປ່ຽນ route ແລະ ຂໍ້ຄວາມ
    }

    public function destroy(DocumentType $documentType) // <-- ປ່ຽນ Type Hint
    {
        // (Optional) ກວດສອບວ່າ Document Type ນີ້ມີເອກະສານຜູກຢູ່ບໍ່
        // if ($documentType->documents()->count() > 0) { ... }
        
        $documentType->delete();

        return redirect()->route('admin.document-types.index')->with('success', 'ລຶບປະເພດເອກະສານສຳເລັດແລ້ວ.'); // <-- ປ່ຽນ route ແລະ ຂໍ້ຄວາມ
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * Display the specified resource.
     */
    public function show(DocumentType $documentType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentType $documentType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentType $documentType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
}
