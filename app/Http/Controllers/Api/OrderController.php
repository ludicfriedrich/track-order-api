<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Lister les commandes",
     *     description="Récupère une liste paginée de commandes avec une option de recherche par nom du client.",
     *     tags={"Commandes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="client_name",
     *         in="query",
     *         description="Terme de recherche pour filtrer les commandes par nom du client.",
     *         required=false,
     *         @OA\Schema(type="string", example="John Doe")
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
     *         description="Liste paginée des commandes.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Liste des commandes"),
     *             @OA\Property(property="orders", type="object")
     *         )
     *     )
     * )
     */

    // Afficher la liste des commandes
    public function index(Request $request)
    {
        // Récupérer le terme de recherche pour le nom du client
        $clientName = $request->query('client_name', '');

        // Récupérer le nombre d'éléments par page (par défaut 10)
        $perPage = $request->query('per_page', 10);

        // Rechercher les commandes par client_name et appliquer la pagination
        $orders = Order::with('orderLines.product')
            ->where('client_name', 'LIKE', "%{$clientName}%")
            ->paginate($perPage);

        // Retourner les résultats paginés
        return response()->json([
            'message' => 'Liste des commandes',
            'orders' => $orders
        ], 200);
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
     *     path="/api/orders",
     *     summary="Créer une commande",
     *     description="Permet de créer une nouvelle commande avec des lignes de commande.",
     *     tags={"Commandes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_name", "client_phone", "order_lines"},
     *             @OA\Property(property="client_name", type="string", example="John Doe", description="Nom du client."),
     *             @OA\Property(property="client_phone", type="string", example="1234567890", description="Numéro de téléphone du client."),
     *             @OA\Property(property="order_lines", type="array", description="Lignes de commande.",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1, description="ID du produit."),
     *                     @OA\Property(property="quantity", type="integer", example=2, description="Quantité du produit.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Commande créée avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Commande créée avec succès."),
     *             @OA\Property(property="order", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur de validation."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */

    // Enregistrer une commande
    public function store(Request $request)
    {
        // Personnalisation des messages d'erreur
        $customMessages = [
            'client_name.required' => 'Le nom du client est obligatoire.',
            'client_name.string' => 'Le nom du client doit être une chaîne de caractères.',
            'client_name.max' => 'Le nom du client ne doit pas dépasser 255 caractères.',
            'client_phone.required' => 'Le numéro de téléphone du client est obligatoire.',
            'client_phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'client_phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'order_lines.required' => 'La liste des lignes de commande est obligatoire.',
            'order_lines.array' => 'Les lignes de commande doivent être envoyées sous forme de tableau.',
            'order_lines.*.id.required' => 'L\'ID du produit est obligatoire.',
            'order_lines.*.id.exists' => 'Le produit sélectionné n\'existe pas.',
            'order_lines.*.quantity.required' => 'La quantité est obligatoire pour chaque produit.',
            'order_lines.*.quantity.integer' => 'La quantité doit être un nombre entier.',
            'order_lines.*.quantity.min' => 'La quantité doit être au moins de 1.',
        ];

        // Validation des données
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'order_lines' => 'required|array',
            'order_lines.*.id' => 'required|exists:products,id',
            'order_lines.*.quantity' => 'required|integer|min:1',
        ], $customMessages);

        DB::beginTransaction();

        try {
            // Création de la commande
            $order = Order::create([
                'user_id' => $request->user()->id,
                'client_name' => $validated['client_name'],
                'client_phone' => $validated['client_phone'],
                'total_price' => 0,
                'status' => 'pending',
            ]);

            $total = 0;

            // Création des lignes de commande
            foreach ($validated['order_lines'] as $lineData) {
                $product = Product::find($lineData['id']);
                $quantity = $lineData['quantity'];
                $unitPrice = $product->price;

                OrderLine::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);

                $total += $unitPrice * $quantity;
            }

            // Mise à jour du prix total de la commande
            $order->update(['total_price' => $total]);

            DB::commit();

            // Retourner la commande avec ses lignes et produits associés
            return response()->json([
                'message' => 'Commande créée avec succès.',
                'order' => $order->load('orderLines.product'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            // Gestion des erreurs
            return response()->json([
                'message' => 'Erreur lors de la création de la commande.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Afficher une commande",
     *     description="Récupère les détails d'une commande spécifique en fonction de son ID.",
     *     tags={"Commandes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la commande à afficher.",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la commande récupérés avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Détails de la commande récupérés avec succès."),
     *             @OA\Property(property="order", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Commande non trouvée.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Commande non trouvée.")
     *         )
     *     )
     * )
     */

    // Afficher les détails d'une commande
    public function show(string $id)
    {
        // Charger la commande avec ses lignes et les produits associés
        $order = Order::with('orderLines.product')->find($id);

        // Vérifier si la commande existe
        if (!$order) {
            return response()->json([
                'message' => 'Commande non trouvée.'
            ], 404);
        }

        // Retourner les détails de la commande
        return response()->json([
            'message' => 'Détails de la commande récupérés avec succès.',
            'order' => $order
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
     *     path="/api/orders/{id}",
     *     summary="Mettre à jour une commande",
     *     description="Permet de mettre à jour les informations d'une commande existante. Les commandes livrées ou payées ne peuvent pas être modifiées.",
     *     tags={"Commandes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la commande à mettre à jour.",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="client_name", type="string", example="Jane Doe", description="Nom du client."),
     *             @OA\Property(property="client_phone", type="string", example="9876543210", description="Numéro de téléphone du client."),
     *             @OA\Property(property="order_lines", type="array", description="Lignes de commande.",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1, description="ID du produit."),
     *                     @OA\Property(property="quantity", type="integer", example=3, description="Quantité du produit.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Commande mise à jour avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Commande mise à jour avec succès."),
     *             @OA\Property(property="order", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Action non autorisée.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vous n'êtes pas autorisé à mettre à jour cette commande.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Commande non modifiable.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Impossible de mettre à jour une commande livrée ou payée.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Commande non trouvée.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Commande non trouvée.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur de validation."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */

    // Mettre à jour une commande
    public function update(Request $request, string $id)
    {
        // Recherche de la commande par ID
        $order = Order::find($id);

        // Vérifier si la commande existe
        if (!$order) {
            return response()->json(['message' => 'Commande non trouvée.'], 404);
        }

        // Vérifier si l'utilisateur est autorisé à mettre à jour la commande
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à mettre à jour cette commande.'], 403);
        }

        // Vérifier si la commande est livrée
        if ($order->status === 'delivered') {
            return response()->json(['message' => 'Impossible de mettre à jour une commande livrée.'], 400);
        }

        // Vérifier si la commande est payée
        if ($order->status === 'paid') {
            return response()->json(['message' => 'Impossible de mettre à jour une commande payée.'], 400);
        }

        // Personnalisation des messages d'erreur
        $customMessages = [
            'client_name.string' => 'Le nom du client doit être une chaîne de caractères.',
            'client_name.max' => 'Le nom du client ne doit pas dépasser 255 caractères.',
            'client_phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'client_phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'order_lines.array' => 'Les lignes de commande doivent être envoyées sous forme de tableau.',
            'order_lines.*.id.required_with' => 'L\'ID du produit est obligatoire lorsque des lignes de commande sont fournies.',
            'order_lines.*.id.exists' => 'Le produit sélectionné n\'existe pas.',
            'order_lines.*.quantity.required_with' => 'La quantité est obligatoire pour chaque produit.',
            'order_lines.*.quantity.integer' => 'La quantité doit être un nombre entier.',
            'order_lines.*.quantity.min' => 'La quantité doit être au moins de 1.',
        ];

        // Validation des données
        $validated = $request->validate([
            'client_name' => 'sometimes|string|max:255',
            'client_phone' => 'sometimes|string|max:20',
            'order_lines' => 'sometimes|array',
            'order_lines.*.id' => 'required_with:order_lines|exists:products,id',
            'order_lines.*.quantity' => 'required_with:order_lines|integer|min:1',
        ], $customMessages);

        DB::beginTransaction();

        try {
            // Mise à jour des informations du client
            $order->update([
                'client_name' => $validated['client_name'] ?? $order->client_name,
                'client_phone' => $validated['client_phone'] ?? $order->client_phone,
            ]);

            // Mise à jour des lignes de commande si fournies
            if (isset($validated['order_lines'])) {
                // Supprimer les anciennes lignes de commande
                $order->orderLines()->delete();

                $total = 0;

                foreach ($validated['order_lines'] as $lineData) {
                    $product = Product::find($lineData['id']);
                    $quantity = $lineData['quantity'];
                    $unitPrice = $product->price;

                    // Créer une nouvelle ligne de commande
                    OrderLine::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                    ]);

                    $total += $unitPrice * $quantity;
                }

                // Mettre à jour le prix total de la commande
                $order->update(['total_price' => $total]);
            }

            DB::commit();

            // Retourner la commande mise à jour avec ses lignes et produits associés
            return response()->json([
                'message' => 'Commande mise à jour avec succès.',
                'order' => $order->load('orderLines.product'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            // Gestion des erreurs
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la commande.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     summary="Supprimer une commande",
     *     description="Permet de supprimer une commande existante en fonction de son ID.",
     *     tags={"Commandes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la commande à supprimer.",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Commande supprimée avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Commande supprimée avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Commande non trouvée.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Commande non trouvée.")
     *         )
     *     )
     * )
     */

    // Supprimer une commande
    public function destroy($id)
    {
        // Recherche de la commande par ID
        $order = Order::find($id);

        // Vérifier si la commande existe
        if (!$order) {
            return response()->json([
                'message' => 'Commande non trouvée.'
            ], 404);
        }

        try {
            // Suppression de la commande
            $order->delete();

            // Réponse JSON en cas de succès
            return response()->json([
                'message' => 'Commande supprimée avec succès.'
            ], 200);
        } catch (\Exception $e) {
            // Gestion des erreurs
            return response()->json([
                'message' => 'Erreur lors de la suppression de la commande.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
