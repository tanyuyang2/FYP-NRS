import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { useNotification } from "../context/NotificationContext";
import { Coins } from "lucide-react";
import api from "../api/axios";
import Loading from "./Loading";
import "./Shop.css";

export default function Shop() {
    const navigate = useNavigate();
    const { user, isLoggedIn, isLoading, refreshUser, showCoinReward } = useAuth();
	const { showNotification } = useNotification();
	const [buying, setBuying] = useState(null);
    const [cosmetics, setCosmetics] = useState([]);
    const [selectedType, setSelectedType] = useState(1);
    const [isShopLoading, setIsShopLoading] = useState(true);

    const filteredCosmetics = cosmetics.filter(
        cosmetic => cosmetic.cosmetic_type_id == selectedType
    );

    useEffect(() => {
        if (!isLoading && !isLoggedIn) {
            navigate("/");
        }
    }, [isLoading, isLoggedIn, navigate]);

    useEffect(() => {
        if (!isLoggedIn) {
            return;
        }

        setIsShopLoading(true);

        api.get("/user/listShopCosmetic.php")
            .then(response => {
                setCosmetics(response.data.cosmetics);
            })
            .catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load shop",
					"error"
				);
            })
			.finally(() => {
				setIsShopLoading(false);
			});
    }, [isLoggedIn]);

	const handleBuy = async (cosmeticId) => {
		if (buying !== null) {
			return;
		}

		setBuying(cosmeticId);
		try {
			const response = await api.post("/user/buyComestic.php", {
				cosmetic_id: cosmeticId
			});

			showCoinReward(response.data.amount);

			setCosmetics(prev =>
				prev.map(cosmetic =>
					cosmetic.cosmetic_id == cosmeticId ? { ...cosmetic, is_owned: 1 }
						: cosmetic
				)
			);

			await refreshUser();

			showNotification(response.data.message, "success");
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Purchase Failed",
				"error"
			);
		} finally {
			setBuying(null);
		}
	};

    if (isLoading || !user || isShopLoading) {
        return <Loading />;
    }

    return (
        <section className="shop">
            <h1>Shop</h1>

            <div className="shop-tabs">
                <button
                    className={selectedType === 1 ? "selected" : ""}
                    onClick={() => setSelectedType(1)}
                >
                    Profile frames
                </button>

                <button
                    className={selectedType === 2 ? "selected" : ""}
                    onClick={() => setSelectedType(2)}
                >
                    Backgrounds
                </button>
            </div>

			<div className="shop-grid">
				{filteredCosmetics.map(cosmetic => (
					<div className="shop-item" key={cosmetic.cosmetic_id}>
						<div className="shop-preview">
							{cosmetic.cosmetic_type_id == 1 ? (
								<div className="shop-avatar">
									<img
										className="shop-profile-picture"
										src={user.profile_picture || "/default-profile.svg"}
										alt=""
									/>

									<img
										className="shop-profile-frame"
										src={cosmetic.image_url}
										alt={cosmetic.name}
									/>
								</div>
							) : (
								<img
									className="shop-background"
									src={cosmetic.image_url}
									alt={cosmetic.name}
								/>
							)}
						</div>

						<h2>{cosmetic.name}</h2>

						<div className="shop-price">
							<Coins size={18} />
							<span>{cosmetic.price}</span>
						</div>

						{cosmetic.is_owned == 1 ? (
							<span className="shop-owned">Owned</span>
						) : (
							<button
								className="shop-buy"
								disabled={buying !== null}
								onClick={() => handleBuy(cosmetic.cosmetic_id)}
							>
								{buying == cosmetic.cosmetic_id ? "Buying..." : "Buy"}
							</button>
						)}
					</div>
				))}
			</div>
        </section>
    );
}