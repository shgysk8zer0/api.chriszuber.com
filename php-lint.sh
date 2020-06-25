#! /bin/bash
# Global variables
extensions=('php' 'phtml')
ignore_dirs=("$(realpath './vendor')")

function lint_file() {
	local filename="$1"
	local extension="${filename##*.}"

	if array_contains "$extension" "${extensions[@]}"; then
		php -l "$filename"
	fi
}

function array_contains () {
	for e in "${@:2}"; do
		[[ "$e" = "$1" ]] && return 0;
	done;

	return 1;
}

function lint_dir() {
	local passed
	passed="$(realpath "$1")"

	if [[ -d "$passed" ]]; then
		if array_contains "$passed" "${ignore_dirs[@]}"; then
			echo "$passed is ignored"
		else
			for path in "$passed"/*; do
				if [ -f "$path" ]; then
					lint_file "$path" || exit 1;
				elif [ -d "$path" ]; then
					lint_dir "$path" || exit 1;
				fi
			done
		fi
	fi
}

if [ $# -eq 0 ]; then
	lint_dir "$(realpath './')"
else
	for path in "$@"; do
		if [ -f "$path" ]; then
			lint_file "$path"
		elif [ -d "$path" ]; then
			lint_dir "$path"
		fi
	done
fi

exit 0
