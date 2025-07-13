<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\BaseController;
use Src\Core\Database;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="Category management endpoints"
 * )
 */
class CategoryController extends BaseController
{
    private Database $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Get all categories",
     *     description="Retrieve a list of all categories with optional filtering and pagination",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=20)
     *     ),
     *     @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         description="Filter by parent category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in category name and description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Categories retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Category")
     *                 ),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function index(): void
    {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            $parentId = $_GET['parent_id'] ?? null;
            $search = $_GET['search'] ?? null;

            // Build query
            $query = "SELECT * FROM categories WHERE 1=1";
            $params = [];

            if ($parentId !== null) {
                if ($parentId === '0' || $parentId === 'null') {
                    $query .= " AND parent_id IS NULL";
                } else {
                    $query .= " AND parent_id = :parent_id";
                    $params['parent_id'] = $parentId;
                }
            }

            if ($search) {
                $query .= " AND (name LIKE :search OR description LIKE :search)";
                $params['search'] = "%{$search}%";
            }

            $query .= " ORDER BY sort_order ASC, name ASC";

            // Get total count
            $countQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
            $total = $this->db->fetch($countQuery, $params)['total'];

            // Add pagination
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT {$limit} OFFSET {$offset}";

            $categories = $this->db->fetchAll($query, $params);

            // Parse metadata JSON
            foreach ($categories as &$category) {
                $category['metadata'] = $category['metadata'] ? json_decode($category['metadata'], true) : null;
            }

            $pagination = [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => (int) $total,
                'last_page' => ceil($total / $limit),
                'from' => $offset + 1,
                'to' => min($offset + $limit, $total)
            ];

            $this->success([
                'categories' => $categories,
                'pagination' => $pagination
            ], 'Categories retrieved successfully');

        } catch (\Exception $e) {
            $this->error('Failed to retrieve categories: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Get category by ID",
     *     description="Retrieve a specific category by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     ),
     *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function show(int $id): void
    {
        try {
            $category = $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);

            if (!$category) {
                $this->error('Category not found', 404);
                return;
            }

            // Parse metadata JSON
            $category['metadata'] = $category['metadata'] ? json_decode($category['metadata'], true) : null;

            $this->success($category, 'Category retrieved successfully');

        } catch (\Exception $e) {
            $this->error('Failed to retrieve category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Create a new category",
     *     description="Create a new category",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "slug"},
     *             @OA\Property(property="name", type="string", example="Technology"),
     *             @OA\Property(property="slug", type="string", example="technology"),
     *             @OA\Property(property="description", type="string", example="Technology related content"),
     *             @OA\Property(property="parent_id", type="integer", example=null),
     *             @OA\Property(property="sort_order", type="integer", example=1),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 example={"color": "#007bff", "icon": "fas fa-laptop"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     ),
     *     @OA\Response(response=400, ref="#/components/responses/ValidationError"),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function store(): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        try {
            $data = $this->getRequestData();

            // Validate required fields
            $errors = $this->validateRequired($data, ['name', 'slug']);
            if (!empty($errors)) {
                $this->error('Validation failed', 400, $errors);
                return;
            }

            // Check if slug already exists
            $existing = $this->db->fetch("SELECT id FROM categories WHERE slug = ?", [$data['slug']]);
            if ($existing) {
                $this->error('Category slug must be unique', 400);
                return;
            }

            // Prepare data
            $categoryData = [
                'name' => $this->sanitizeString($data['name']),
                'slug' => $this->sanitizeString($data['slug']),
                'description' => isset($data['description']) ? $this->sanitizeString($data['description']) : null,
                'parent_id' => $data['parent_id'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $categoryId = $this->db->insert('categories', $categoryData);

            // Fetch created category
            $category = $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$categoryId]);
            $category['metadata'] = $category['metadata'] ? json_decode($category['metadata'], true) : null;

            $this->success($category, 'Category created successfully', 201);

        } catch (\Exception $e) {
            $this->error('Failed to create category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Update category",
     *     description="Update an existing category",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Technology"),
     *             @OA\Property(property="slug", type="string", example="technology"),
     *             @OA\Property(property="description", type="string", example="Technology related content"),
     *             @OA\Property(property="parent_id", type="integer", example=null),
     *             @OA\Property(property="sort_order", type="integer", example=1),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 example={"color": "#007bff", "icon": "fas fa-laptop"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     ),
     *     @OA\Response(response=400, ref="#/components/responses/ValidationError"),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function update(int $id): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        try {
            // Check if category exists
            $category = $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
            if (!$category) {
                $this->error('Category not found', 404);
                return;
            }

            $data = $this->getRequestData();

            // Check if slug already exists (excluding current category)
            if (isset($data['slug'])) {
                $existing = $this->db->fetch(
                    "SELECT id FROM categories WHERE slug = ? AND id != ?",
                    [$data['slug'], $id]
                );
                if ($existing) {
                    $this->error('Category slug must be unique', 400);
                    return;
                }
            }

            // Prepare update data
            $updateData = ['updated_at' => date('Y-m-d H:i:s')];

            $allowedFields = ['name', 'slug', 'description', 'parent_id', 'sort_order', 'is_active'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    if (in_array($field, ['name', 'slug', 'description'])) {
                        $updateData[$field] = $this->sanitizeString($data[$field]);
                    } else {
                        $updateData[$field] = $data[$field];
                    }
                }
            }

            if (isset($data['metadata'])) {
                $updateData['metadata'] = json_encode($data['metadata']);
            }

            $this->db->update('categories', $updateData, 'id = ?', [$id]);

            // Fetch updated category
            $updatedCategory = $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
            $updatedCategory['metadata'] = $updatedCategory['metadata'] ? json_decode($updatedCategory['metadata'], true) : null;

            $this->success($updatedCategory, 'Category updated successfully');

        } catch (\Exception $e) {
            $this->error('Failed to update category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Delete category",
     *     description="Delete a category and all its subcategories",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function destroy(int $id): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        try {
            // Check if category exists
            $category = $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
            if (!$category) {
                $this->error('Category not found', 404);
                return;
            }

            // Delete subcategories first
            $this->db->delete('categories', 'parent_id = ?', [$id]);

            // Delete the category
            $this->db->delete('categories', 'id = ?', [$id]);

            $this->success([], 'Category deleted successfully');

        } catch (\Exception $e) {
            $this->error('Failed to delete category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/categories/tree",
     *     tags={"Categories"},
     *     summary="Get category tree",
     *     description="Retrieve categories organized in a hierarchical tree structure",
     *     @OA\Response(
     *         response=200,
     *         description="Category tree retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category tree retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/Category"),
     *                         @OA\Schema(
     *                             @OA\Property(
     *                                 property="children",
     *                                 type="array",
     *                                 @OA\Items(ref="#/components/schemas/Category")
     *                             )
     *                         )
     *                     }
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function tree(): void
    {
        try {
            $categories = $this->db->fetchAll("SELECT * FROM categories ORDER BY sort_order ASC, name ASC");

            // Parse metadata JSON
            foreach ($categories as &$category) {
                $category['metadata'] = $category['metadata'] ? json_decode($category['metadata'], true) : null;
            }

            // Build tree structure
            $tree = $this->buildCategoryTree($categories);

            $this->success($tree, 'Category tree retrieved successfully');

        } catch (\Exception $e) {
            $this->error('Failed to retrieve category tree: ' . $e->getMessage(), 500);
        }
    }

    private function buildCategoryTree(array $categories, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $category['children'] = $this->buildCategoryTree($categories, (int)$category['id']);
                $tree[] = $category;
            }
        }

        return $tree;
    }
}
