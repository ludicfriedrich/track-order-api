<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
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

    // Récupérer les informations de l'utilisateur connecté
    public function getUser(Request $request)
    {
        return response()->json([
            'message' => 'Utilisateur récupéré avec succès.',
            'user' => $request->user()
        ], 200);
    }
}
