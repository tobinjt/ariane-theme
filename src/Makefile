lint:
	for file in *.php; do \
		./phplint-wrapper "$${file}" \
			| grep ^= \
			| grep -v -F -e '?:?:' \
				-e 'notice: throwing JsonException' || true; \
	done

test:
	(cd .. && phpunit --coverage-html "${HOME}/tmp/coverage")
