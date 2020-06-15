lint:
	for file in *.php; do \
		./phplint-wrapper "$${file}" \
			| grep ^= \
			| grep -v -F '?:?:' || true; \
	done

test:
	(cd .. && phpunit)
