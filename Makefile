# Makefile for building the project

build_dir=$(CURDIR)/build/artifacts

ifeq (, $(shell which krankerl))
$(error "No krankerl in $(PATH), see https://github.com/ChristophWurst/krankerl")
endif

all:
	krankerl up

appstore:
	krankerl package

clean:
	rm -rf $(build_dir)
	rm -rf vendor
