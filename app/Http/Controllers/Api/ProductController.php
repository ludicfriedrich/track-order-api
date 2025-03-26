<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Lister les produits",
     *     description="Récupère une liste paginée de produits avec une option de recherche par nom.",
     *     tags={"Produits"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Terme de recherche pour filtrer les produits par nom.",
     *         required=false,
     *         @OA\Schema(type="string", example="Laptop")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page (par défaut 10).",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste paginée des produits.",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Laptop Pro"),
     *                     @OA\Property(property="description", type="string", example="High-end laptop"),
     *                     @OA\Property(property="price", type="number", format="float", example=1200.99),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-26T10:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-26T10:00:00.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="total", type="integer", example=50),
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="last_page", type="integer", example=5),
     *             @OA\Property(property="next_page_url", type="string", example="http://example.com/api/products?page=2"),
     *             @OA\Property(property="prev_page_url", type="string", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Paramètres invalides.")
     *         )
     *     )
     * )
     */

    // Afficher la liste des produits
    public function index(Request $request)
    {
        // Récupérer le terme de recherche depuis les paramètres de la requête
        $search = $request->query('search', '');

        // Récupérer le nombre d'éléments par page (par défaut 10)
        $perPage = $request->query('per_page', 10);

        // Rechercher les produits par nom et appliquer la pagination
        $products = Product::where('name', 'LIKE', "%{$search}%")
            ->paginate($perPage);

        // Retourner les résultats paginés
        return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Ajouter un produit",
     *     description="Permet d'ajouter un nouveau produit avec validation des données.",
     *     tags={"Produits"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "price", "stock"},
     *             @OA\Property(property="name", type="string", example="Laptop Pro", description="Nom du produit."),
     *             @OA\Property(property="description", type="string", example="Un ordinateur portable haut de gamme.", description="Description du produit."),
     *             @OA\Property(property="price", type="number", format="float", example=1200.99, description="Prix du produit."),
     *             @OA\Property(property="stock", type="integer", example=50, description="Quantité en stock.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Produit créé avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Produit créé avec succès."),
     *             @OA\Property(property="product", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Laptop Pro"),
     *                 @OA\Property(property="description", type="string", example="Un ordinateur portable haut de gamme."),
     *                 @OA\Property(property="price", type="number", format="float", example=1200.99),
     *                 @OA\Property(property="stock", type="integer", example=50),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="26/03/2025 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="26/03/2025 10:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur de validation."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "name": {"Le nom est obligatoire."},
     *                 "price": {"Le prix doit être un nombre."}
     *             })
     *         )
     *     )
     * )
     */

    //Ajouter un produit
    public function store(Request $request)
    {

        // Personnalisation des messages d'erreur
        $custom_messages = [
            'name.required' => 'Le nom est obligatoire.',
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',

            'description.required' => 'La description est obligatoire.',
            'email.string' => 'La description doit être une chaîne de caractères.',

            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',

            'stock.required' => 'Le stock est obligatoire.',
            'stock.integer' => 'Le stock doit être un nombre entier.'
        ];

        // Validation des données
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer'
        ], $custom_messages);

        // Vérification si la validation échoue
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation.',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock
        ]);

        // Réponse JSON
        return response()->json([
            'message' => 'Produit créé avec succès.',
            'product' => $product
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Afficher un produit",
     *     description="Récupère les détails d'un produit spécifique en fonction de son ID.",
     *     tags={"Produits"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du produit à afficher.",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du produit récupérés avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Laptop Pro"),
     *             @OA\Property(property="description", type="string", example="Un ordinateur portable haut de gamme."),
     *             @OA\Property(property="price", type="number", format="float", example=1200.99),
     *             @OA\Property(property="stock", type="integer", example=50),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="26/03/2025 10:00:00"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="26/03/2025 10:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produit non trouvé.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Produit non trouvé.")
     *         )
     *     )
     * )
     */

    // Afficher un produit
    public function show(string $id)
    {
        // Récupérer le produit par son identifiant
        $product = Product::find($id);

        // Vérifier si le produit existe
        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        // Retourner le produit
        return response()->json([
            'message' => 'Détails du produit récupéré avec succès.',
            'product' => $product
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Mettre à jour un produit",
     *     description="Permet de mettre à jour les informations d'un produit existant.",
     *     tags={"Produits"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du produit à mettre à jour.",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Laptop Pro", description="Nom du produit."),
     *             @OA\Property(property="description", type="string", example="Un ordinateur portable haut de gamme.", description="Description du produit."),
     *             @OA\Property(property="price", type="number", format="float", example=1200.99, description="Prix du produit."),
     *             @OA\Property(property="stock", type="integer", example=50, description="Quantité en stock.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produit mis à jour avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Produit mis à jour avec succès."),
     *             @OA\Property(property="product", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Laptop Pro"),
     *                 @OA\Property(property="description", type="string", example="Un ordinateur portable haut de gamme."),
     *                 @OA\Property(property="price", type="number", format="float", example=1200.99),
     *                 @OA\Property(property="stock", type="integer", example=50),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="26/03/2025 10:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="26/03/2025 10:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produit non trouvé.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Produit non trouvé.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur de validation."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "name": {"Le nom doit être une chaîne de caractères."},
     *                 "price": {"Le prix doit être un nombre."}
     *             })
     *         )
     *     )
     * )
     */

    // Mettre à jour un produit
    public function update(Request $request, string $id)
    {
        // Personnalisation des messages d'erreur
        $custom_messages = [
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'stock.integer' => 'Le stock doit être un nombre entier.',
        ];

        // Validation des données
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
        ], $custom_messages);

        // Recherche du produit par ID
        $product = Product::find($id);

        // Vérifier si le produit existe
        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé.'], 404);
        }

        // Mise à jour des données du produit
        $product->update($validated);

        // Réponse JSON
        return response()->json([
            'message' => 'Produit mis à jour avec succès.',
            'product' => $product
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Supprimer un produit",
     *     description="Permet de supprimer un produit existant en fonction de son ID.",
     *     tags={"Produits"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du produit à supprimer.",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produit supprimé avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Produit supprimé avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produit non trouvé.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Produit non trouvé.")
     *         )
     *     )
     * )
     */

    // Supprimer un produit
    public function destroy(string $id)
    {
        // Recherche du produit par ID
        $product = Product::find($id);

        // Vérifier si le produit existe
        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        // Suppression du produit
        $product->delete();

        // Réponse JSON
        return response()->json(['message' => 'Produit supprimé avec succès.'], 200);
    }
}
