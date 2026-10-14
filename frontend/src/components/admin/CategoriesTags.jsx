import { useEffect, useState } from "react";
import { useNotification } from "../../context/NotificationContext";
import { Plus, X } from "lucide-react";
import api from "../../api/axios";
import Loading from "../Loading";
import CategoryTagModal from "./CategoryTagModal";
import "./CategoriesTags.css";

export default function CategoriesTags() {
	const { showNotification } = useNotification();
	const [categories, setCategories] = useState([]);
	const [tags, setTags] = useState([]);
	const [isCategoriesLoading, setIsCategoriesLoading] = useState(true);
	const [showAddCategoryModal, setShowAddCategoryModal] = useState(false);
	const [editingCategory, setEditingCategory] = useState(null);
	const [showAddTagModal, setShowAddTagModal] = useState(false);
	const [isLoading, setIsLoading] = useState(false);
	const [refreshCount, setRefreshCount] = useState(0);

	useEffect(() => {
		api.get("/admin/listCategoriesTags.php")
			.then(response => {
				setCategories(response.data.categories);
				setTags(response.data.tags);
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load categories and tags",
					"error"
				);
			})
			.finally(() => {
				setIsCategoriesLoading(false);
			});
	}, [showNotification, refreshCount]);

	const handleAddCategory = async name => {
		try {
			const response = await api.post("/admin/addCategory.php", {
				name
			});

			showNotification(response.data.message, "success");
			setRefreshCount(prev => prev + 1);
			return true;
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Failed to add category",
				"error"
			);
			return false;
		}
	};

	const handleEditCategory = async name => {
		try {
			const response = await api.post("/admin/editCategory.php", {
				category_id: editingCategory.category_id,
				name
			});

			showNotification(response.data.message, "success");
			setRefreshCount(prev => prev + 1);
			return true;
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Failed to update category",
				"error"
			);
			return false;
		}
	};

	const handleDeleteCategory = async categoryId => {
		if (isLoading) {
			return;
		}
		setIsLoading(true);

		try {
			const response = await api.post("/admin/deleteCategory.php", {
				category_id: categoryId
			});

			showNotification(response.data.message, "success");
			setRefreshCount(prev => prev + 1);
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Failed to delete category",
				"error"
			);
		} finally {
			setIsLoading(false);
		}
	};

	const handleAddTag = async name => {
		try {
			const response = await api.post("/admin/addTag.php", {
				name
			});

			showNotification(response.data.message, "success");
			setRefreshCount(prev => prev + 1);
			return true;
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Failed to add tag",
				"error"
			);
			return false;
		}
	};

	const handleDeleteTag = async tagId => {
		if (isLoading) {
			return;
		}

		setIsLoading(true);

		try {
			const response = await api.post("/admin/deleteTag.php", {
				tag_id: tagId
			});

			showNotification(response.data.message, "success");
			setRefreshCount(prev => prev + 1);
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Failed to delete tag",
				"error"
			);
		} finally {
			setIsLoading(false);
		}
	};

	if (isCategoriesLoading) {
		return <Loading />;
	}

	return (
		<section className="categories-tags">
			<div className="categories-header">
				<div>
					<h1>Categories & Tags</h1>
					<p className="categories-subtitle">
						{categories.length} categories · {tags.length} {tags.length == 1 ? "tag" : "tags"}
					</p>
				</div>

				<button className="categories-add" onClick={() => setShowAddCategoryModal(true)}>
					<Plus size={18} />
					Add Category
				</button>
			</div>

			<div className="categories-list">
				{categories.map(category => (
					<div className="category-item" key={category.category_id}>
						<div className="category-details">
							<p className="category-name">
								{category.category_name}
							</p>
							<p className="category-count">
								{category.article_count} {category.article_count == 1 ? "article" : "articles"}
							</p>
						</div>

						<div className="category-actions">
							<button className="category-edit" onClick={() => setEditingCategory(category)}>
								Edit
							</button>

							<button
								className="category-delete"
								onClick={() => handleDeleteCategory(category.category_id)}
								disabled={category.article_count > 0 || isLoading}
							>
								Delete Category
							</button>
						</div>
					</div>
				))}
			</div>

			<div className="tags-section">
				<h2>Tags</h2>
				<p className="tags-subtitle">
					{tags.length} {tags.length == 1 ? "tag" : "tags"}
				</p>

				<div className="tags-list">
					{tags.length === 0 ? (
						<p className="tags-empty">No tags</p>
					) : (
						tags.map(tag => (
							<div className="tag-item" key={tag.tag_id}>
								<span>{tag.name}</span>

								<button 
									className="tag-delete"
									onClick={() => handleDeleteTag(tag.tag_id)}
									disabled={isLoading}
								>
									<X size={16} />
								</button>
							</div>
						))
					)}
				</div>

				<div className="tags-actions">
					<button className="tags-add" onClick={() => setShowAddTagModal(true)}>
						<Plus size={18} />
						Add Tag
					</button>
				</div>
			</div>
			{showAddCategoryModal && (
				<CategoryTagModal
					type="category"
					onConfirm={handleAddCategory}
					onClose={() => setShowAddCategoryModal(false)}
				/>
			)}
			{editingCategory && (
				<CategoryTagModal
					type="category"
					isEditing={true}
					initialName={editingCategory.category_name}
					onConfirm={handleEditCategory}
					onClose={() => setEditingCategory(null)}
				/>
			)}
			{showAddTagModal && (
				<CategoryTagModal
					type="tag"
					onConfirm={handleAddTag}
					onClose={() => setShowAddTagModal(false)}
				/>
			)}
		</section>
	);
}