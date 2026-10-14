import { useState } from "react";
import "./AdminActionModal.css";

export default function CategoryTagModal({ type, isEditing = false, initialName = "", onConfirm, onClose }) {
	const [name, setName] = useState(initialName);
	const [isSubmitting, setIsSubmitting] = useState(false);
	const label = type === "category" ? "Category" : "Tag";

	const handleSubmit = async e => {
		e.preventDefault();
		if (!name.trim() || isSubmitting) {
			return;
		}
		setIsSubmitting(true);

		try {
			const success = await onConfirm(name);
			if (success) {
				onClose();
			}
		} finally {
			setIsSubmitting(false);
		}
	};

	return (
		<div
			className="admin-action-overlay"
			onClick={() => {
				if (!isSubmitting) {
					onClose();
				}
			}}
		>
			<div
				className="admin-action-modal"
				onClick={e => e.stopPropagation()}
			>
				<h2>{isEditing ? "Edit" : "Add"} {label}</h2>

				<form onSubmit={handleSubmit}>
					<label htmlFor="category-tag-name">
						{label} name
					</label>

					<input
						className="action-name"
						id="category-tag-name"
						type="text"
						value={name}
						onChange={e => setName(e.target.value)}
						maxLength={30}
						disabled={isSubmitting}
						required
					/>

					<div className="action-buttons">
						<button
							type="button"
							className="action-cancel"
							onClick={onClose}
							disabled={isSubmitting}
						>
							Cancel
						</button>

						<button
							type="submit"
							className="action-save"
							disabled={isSubmitting || !name.trim()}
						>
							{isEditing ? "Save" : "Add"}
						</button>
					</div>
				</form>
			</div>
		</div>
	);
}