CREATE MATERIALIZED VIEW if not exists ShowCompanyByRubricAggregation (
    `year` UInt16,
    `month` UInt8,
    `dayOfMonth` UInt8,
    `quartet` UInt8,
    `isoWeek` UInt8,
    `companyId` UInt32,
    `rubricId` UInt32,
    `showCount` UInt16
)
    ENGINE = SummingMergeTree
    PARTITION BY (year * 100) + month
    ORDER BY (year, quartet, month, isoWeek, dayOfMonth, companyId, rubricId)
    SETTINGS index_granularity = 8192
    POPULATE AS
SELECT toYear(ce.eventDateTimeOnClient)                                                     AS year,
       toMonth(ce.eventDateTimeOnClient)                                                    AS month,
       toDayOfMonth(ce.eventDateTimeOnClient)                                               AS dayOfMonth,
       toQuarter(ce.eventDateTimeOnClient)                                                  AS quartet,
       toISOWeek(ce.eventDateTimeOnClient)                                                  AS isoWeek,
       ce.companyId,
       ce.rubricId,
       if(and (ce.showCompanyCardInList = 1, ce.listType = 'rubric'), 1, 0) as showCount
FROM CompanyEvent AS ce;
