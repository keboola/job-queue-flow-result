# job-queue-flow-result

Data model for Conditional Flow job results, shared between the job queue daemon and flow layers.

Pure data objects (no business logic): the flow-result document, its builder, and the task / phase /
condition result entries. Both the flow layer (which builds the result) and the daemon (which projects
live created/processing statuses) depend on this library, so the result structure lives in one place.

## License

MIT licensed, see [LICENSE](./LICENSE) file.
