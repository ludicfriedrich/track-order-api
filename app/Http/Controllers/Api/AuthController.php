<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Utilisez un token Bearer pour accéder aux routes protégées."
 * )
 */


class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Inscription d'un nouvel utilisateur",
     *     description="Permet de créer un nouvel utilisateur et de générer un token d'authentification.",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Ludic Test", description="Nom de l'utilisateur."),
     *             @OA\Property(property="email", type="string", format="email", example="ludic.test@example.com", description="Adresse email de l'utilisateur."),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Mot de passe de l'utilisateur."),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123", description="Confirmation du mot de passe.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Utilisateur créé avec succès."),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Ludic Test"),
     *                 @OA\Property(property="email", type="string", example="ludic.test@example.com")
     *             ),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur de validation."),
     *             @OA\Property(property="errors", type="object", example={"email": {"Cette adresse email est déjà utilisée."}})
     *         )
     *     )
     * )
     */

    // Inscription
    public function register(Request $request)
    {
        // Personnalisation des messages d'erreur
        $custom_messages = [
            'name.required' => 'Le nom est obligatoire.',
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',

            'email.required' => 'L\'adresse email est obligatoire.',
            'email.string' => 'L\'adresse email doit être une chaîne de caractères.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',

            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ];

        // Validation des données
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ], $custom_messages);

        // Vérification si la validation échoue
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Création de l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Création du token
        $token = $user->createToken('authToken')->plainTextToken;

        // Réponse JSON
        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Connexion d'un utilisateur",
     *     description="Permet à un utilisateur de se connecter et de recevoir un token d'authentification.",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="ludic.test@example.com", description="Adresse email de l'utilisateur."),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Mot de passe de l'utilisateur.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Connexion réussie !"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Ludic Test"),
     *                 @OA\Property(property="email", type="string", example="Ludic.Test@example.com")
     *             ),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants incorrects.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Les identifiants fournis sont incorrects."),
     *             @OA\Property(property="errors", type="object", example={"email": {"Les identifiants fournis sont incorrects."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur de validation."),
     *             @OA\Property(property="errors", type="object", example={"email": {"L'adresse email est obligatoire."}})
     *         )
     *     )
     * )
     */

    // Connexion
    public function login(Request $request)
    {
        // Personnalisation des messages d'erreur
        $custom_messages = [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.string' => 'L\'adresse email doit être une chaîne de caractères.',
            'email.email' => 'L\'adresse email doit être valide.',

            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
        ];

        // Validation des données
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ], $custom_messages);

        // Vérification si la validation échoue
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérification des identifiants
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Les identifiants fournis sont incorrects.',
                'errors' => [
                    'email' => ['Les identifiants fournis sont incorrects.']
                ]
            ], 401);
        }

        // Création du token
        $token = $user->createToken('authToken')->plainTextToken;

        // Réponse JSON
        return response()->json([
            'message' => 'Connexion réussie !',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Déconnexion de l'utilisateur",
     *     description="Révoque tous les tokens de l'utilisateur connecté.",
     *     tags={"Authentification"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie !")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Aucun utilisateur authentifié.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Aucun utilisateur authentifié.")
     *         )
     *     )
     * )
     */
    // Déconnexion
    public function logout(Request $request)
    {
        // Vérification de l'authentification
        if (!$request->user()) {
            return response()->json([
                'message' => 'Aucun utilisateur authentifié.'
            ], 401);
        }

        // Révocation de tous les tokens
        $request->user()->tokens()->delete();

        // Réponse JSON
        return response()->json([
            'message' => 'Déconnexion réussie !'
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Récupérer les informations de l'utilisateur connecté",
     *     description="Retourne les informations de l'utilisateur actuellement authentifié.",
     *     tags={"Authentification"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations de l'utilisateur récupérées avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Utilisateur récupéré avec succès."),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Ludic Test"),
     *                 @OA\Property(property="email", type="string", example="ludic.test@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Aucun utilisateur authentifié.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Aucun utilisateur authentifié.")
     *         )
     *     )
     * )
     */

    // Récupérer les informations de l'utilisateur connecté
    public function getUser(Request $request)
    {
        return response()->json([
            'message' => 'Utilisateur récupéré avec succès.',
            'user' => $request->user()
        ], 200);
    }
}
