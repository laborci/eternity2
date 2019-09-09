import AdminApplication from "zengular-codex/admin-app";
import menu             from "./src/menu";
import "./src/plugin-loader";

new (class extends AdminApplication {
	initialize() {
		super.initialize();
		this.menu = menu;
	}
})();