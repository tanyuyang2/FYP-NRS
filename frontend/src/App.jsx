import { BrowserRouter, Routes, Route } from "react-router-dom";
import { AuthProvider } from "./context/AuthContext";
import { ModalProvider } from "./context/ModalContext";
import { NotificationProvider } from "./context/NotificationContext";
import SiteLayout from "./components/SiteLayout";
import LandingPage from "./pages/LandingPage";
import SearchPage from "./pages/SearchPage";
import ArticlePage from "./pages/ArticlePage";
import CreateArticlePage from "./pages/CreateArticlePage";
import EditAccountPage from "./pages/EditAccountPage";
import ProfilePage from "./pages/ProfilePage"
import EditProfilePage from "./pages/EditProfilePage"
import UserProfilePage from "./pages/UserProfilePage";
import EditDraftPage from "./pages/EditDraftPage";
import EditPublishedPage from "./pages/EditPublishedPage";
import BookmarkPage from "./pages/BookmarkPage";
import NotificationPage from "./pages/NotifcationPage";
import ShopPage from "./pages/ShopPage";
import ResetPasswordPage from "./pages/ResetPasswordPage";
import TasksPage from "./pages/TasksPage";
import AdminLayout from "./components/admin/AdminLayout";
import ReportedContent from "./components/admin/ReportedContent";
import ResolvedReport from "./components/admin/ResolvedReport";
import ModerationRecord from "./components/admin/ModerationRecord";
import UserAccount from "./components/admin/UserAccount";
import CategoriesTags from "./components/admin/CategoriesTags";
import ModalRoot from "./components/ModalRoot";
import NotificationRoot from "./components/NotificationRoot";

export default function App() {
	return (
		<BrowserRouter>
			<AuthProvider>
				<ModalProvider>
					<NotificationProvider>
						<Routes>
							<Route element={<SiteLayout />}>
								<Route path="/" element={<LandingPage />} />
								<Route path="/search" element={<SearchPage />} />
								<Route path="/create" element={<CreateArticlePage />} />
								<Route path="/article/:id" element={<ArticlePage />} />
								<Route path="/settings" element={<EditAccountPage />} />
								<Route path="/profile" element={<ProfilePage />} />
								<Route path="/edit-profile" element={<EditProfilePage />} />
								<Route path="/profile/:id" element={<UserProfilePage />} />
								<Route path="/edit-draft" element={<EditDraftPage />} />
								<Route path="/edit-published" element={<EditPublishedPage />} />
								<Route path="/bookmark" element={<BookmarkPage />} />
								<Route path="/notification" element={<NotificationPage />} />
								<Route path="/shop" element={<ShopPage />} />
								<Route path="/tasks" element={<TasksPage />} />
							</Route>
							<Route path="/reset-password" element={<ResetPasswordPage />} />
							<Route path="/admin" element={<AdminLayout />}>
								<Route index element={<ReportedContent />} />
								<Route path="resolved" element={<ResolvedReport />} />
								<Route path="moderation" element={<ModerationRecord />} />
								<Route path="users" element={<UserAccount />} />
								<Route path="categories-tags" element={<CategoriesTags />} />
							</Route>
						</Routes>
						<ModalRoot />
						<NotificationRoot />
					</NotificationProvider>
				</ModalProvider>
			</AuthProvider>
		</BrowserRouter>
	);
}