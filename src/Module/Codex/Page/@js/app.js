import AdminApplication from "z-codex/admin-app";
import menu             from "./src/menu";
import "./src/plugin-loader";

new (class extends AdminApplication {
	initialize() {
		super.initialize();
		this.menu = menu;
	}
})();