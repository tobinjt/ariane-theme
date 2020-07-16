lint:
	for file in *.php; do \
		./phplint-wrapper "$${file}" \
			| grep ^= \
			| grep -v -F -e '?:?:' \
				-e 'notice: throwing JsonException' || true; \
	done \
		| sed -e 's/^==== //' \
		| natural_sort \
		| uniq

test:
	(cd .. && phpunit --coverage-html "${HOME}/tmp/coverage")
