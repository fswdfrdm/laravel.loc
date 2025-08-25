<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::all();
        return view('items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $status = Item::getStatus();
        return view('items.create', compact('status'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:255',
            'status' => 'required|in:Allowed,Prohibited',
        ]);

        Item::create($request->all());

        return redirect()->route('items.index')->with('success', 'Строка успешно добавлена.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        $status = Item::getStatus();
        return view('items.edit', compact('item', 'status'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $request->validate([
            'content' => 'required|string|max:255',
            'status' => 'required|in:Allowed,Prohibited',
        ]);

        $item->update($request->all());

        return redirect()->route('items.index')->with('success', 'Строка успешно изменена.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Строка успешно удалена.');
    }
}
