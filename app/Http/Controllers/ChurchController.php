namespace App\Http\Controllers;

use App\Models\Church;
use Illuminate\Http\Request;

class ChurchController extends Controller
{
    public function index()
    {
        $churches = Church::all();
        return response()->json($churches);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
        ]);

        $church = Church::create($validated);
        return response()->json($church, 201);
    }

    public function show(Church $church)
    {
        return response()->json($church);
    }

    public function update(Request $request, Church $church)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
        ]);

        $church->update($validated);
        return response()->json($church);
    }

    public function destroy(Church $church)
    {
        $church->delete();
        return response()->json(null, 204);
    }
}
