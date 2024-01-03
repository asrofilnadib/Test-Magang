<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use http\Env\Response;
use Illuminate\Console\View\Components\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManager;
use function Laravel\Prompts\alert;
use function Laravel\Prompts\error;

class BooksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()){
          $user = Auth::user();
          $books = $user->getRole() === 'admin' ? Book::oldest()->paginate(8) : $user->books;
          return view('book', [
            'books' => $books,
            'categories' => Category::all(),
          ]);
        } else {
          Alert::warning('', 'You need to login in order to access books page.');
        }

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
    public function add(Request $request)
    {
//        dd($request->all());
        $validatedData = $request->validate([
            'title' => 'required|unique:books|max:255',
            'isbn' => 'required',
            'subtitle' => 'required',
            'author' => 'required',
            'publisher' => 'required',
            'image' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'file_pdf' => 'required|mimes:pdf|max:2048',
            'pages' => 'required|integer|min:1',
            'description' => 'required',
        ]);

        $pdfFile = $request->file('file_pdf');
        $image = $request->file('image');

        $pdfPath = $pdfFile->store('pdfs', 'public');
        $imagePath = $image->store('images', 'public');

        $validatedData['user_id'] = auth()->user()->id;
        $validatedData['category_id'] = $request->input('category_id');
        $validatedData['pdf_file'] = $pdfPath;
        $validatedData['image'] = $imagePath;

       /* $manager = new ImageManager(new Intervention\Image\Drivers\Gd\Driver());
        $image = $manager->read($validatedData['image']);
        $image->resize(500, 300)->save();*/

        Book::create($validatedData);

        return redirect('/api/books')->with('success', 'Prouduct created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('dashboard.show', [
          'books' => Book::where('id', $id)->first(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('dashboard.edit', [
            'books' => Book::findOrFail($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
//        dd($request->all());
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'isbn' => 'required',
            'subtitle' => 'required',
            'author' => 'required',
            'publisher' => 'required',
            'pages' => 'required|integer|min:1',
            'description' => 'required',
        ]);

        $validatedData['user_id'] = auth()->user()->id;

        Book::where('id', $id)->update($validatedData);

        return redirect()->route('book')->with('update', 'Book has been updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Book::findOrFail($id);
        $product->delete();

        return back();
    }

    public function downloadPDF($filename)
    {
        $path = storage_path('app/public/pdfs/'. $filename);

        if (file_exists($path)){
            return response()->download($path, $filename);
        } else {
            abort(404);
        }
    }
}
