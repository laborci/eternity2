import ListPreprocessPlugin from "z-codex/plugin/types/ListPreprocessPlugin";

@ListPreprocessPlugin.register()
export default class ListPreprocessUser extends ListPreprocessPlugin {

	preprocess(row) {
		if (row.roles instanceof Array && row.roles.includes('admin')) row.userType = '<i class="fas fa-user-crown"></i>';
		else  if (row.status === 'active') row.userType = '<i class="fas fa-user"></i>';
		else  row.userType = '<i class="fal fa-user"></i>';
	}

}
